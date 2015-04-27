<?php
/**
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL).
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @category   	Payment Gateway
* @package    	MercadoPago
* @author      	Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
* @copyright  	Copyright (c) MercadoPago [http://www.mercadopago.com]
* @license    	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

require_once(Mage::getBaseDir('lib') . '/mercadopago/mercadopago.php');

class MercadoPago_Transparent_Model_Transparent extends Mage_Payment_Model_Method_Abstract{
    
    //configura o block do formulario e de informações sobre o pagamento
    protected $_formBlockType = 'mercadopago_transparent/form';
    protected $_infoBlockType = 'mercadopago_transparent/info';
    
    protected $_code = 'mercadopago_transparent';
    
    protected $_canSaveCc = false;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canVoid = true;
    protected $_canCancelInvoice = true;
    protected $_isInitializeNeeded = true;
    protected $_canFetchTransactionInfo     = true;
    protected $_canCreateBillingAgreement   = true;
    protected $_canReviewPayment            = true;
    
    public function initialize($paymentAction, $stateObject) {
	
	//verifica se o pagamento não é boleto, caso seja não tem card_token_id
        if($this->getInfoInstance()->getAdditionalInformation('payment_type_id') != "ticket" && $this->getInfoInstance()->getAdditionalInformation('card_token_id') == ""):
	    Mage::throwException(Mage::helper('mercadopago_transparent')->__('Verify the form data or wait until the validation of the payment data'));
	    return false;
	endif;
	
	
	//continua o processo de pagamento
	$response = $this->postPago();
    
	if($response !== false):
	
	    $payment = $response['response'];
	    
	    //set order_id
	    $order = Mage::getModel('sales/order')->loadByIncrementId($payment['external_reference']);
	    
	    //set status
	    $this->getInfoInstance()->setAdditionalInformation('status', $payment['status']);
	    $this->getInfoInstance()->setAdditionalInformation('status_detail', $payment['status_detail']);
	    
	    return true;
	endif;
    
	return false;
    }
    
    public function assignData($data){
        
        // route /checkout/onepage/savePayment
        if(!($data instanceof Varien_Object)){
            $data = new Varien_Object($data);
        }
        
        //get array info
        $info_form = $data->getData();
	    
        $info = $this->getInfoInstance();
	$info->setAdditionalInformation('payment_type_id', "credit_card");
        $info->setAdditionalInformation('card_token_id', $info_form['card_token_id']);
        $info->setAdditionalInformation('payment_method', $info_form['payment_method']);
        $info->setAdditionalInformation('installments', $info_form['installments']);
        $info->setAdditionalInformation('doc_number', $info_form['doc_number']);
	
	//caso tenha banco, adiciona nas informações adicionais
	if(isset($info_form['issuers'])){
	    $info->setAdditionalInformation('issuers', $info_form['issuers']);
	}
	
	if($info_form['card_token_id'] != ""):
	    $info->setAdditionalInformation('expiration_date', $info_form['cardExpirationMonth'] . "/" . $info_form['cardExpirationYear']);
	    $info->setAdditionalInformation('cardholderName', $info_form['cardholderName']);
	    $info->setAdditionalInformation('trunc_card', $info_form['trunc_card']);
	endif;
	
        
        //caso seja não tenha card_token_id
        if($info_form['card_token_id'] == ""):
	    Mage::throwException(Mage::helper('mercadopago_transparent')->__('Verify the form data or wait until the validation of the payment data'));
	    return false;
	endif;
	
	
        return $this;
    }
    
    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get admin checkout session namespace
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getAdminCheckout() {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Retrieves Quote
     *
     * @param integer $quoteId
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId = null) {
        if (!empty($quoteId)) {
            return Mage::getModel('sales/quote')->load($quoteId);
        }
        else {
            if(Mage::app()->getStore()->isAdmin()) {
                return $this->_getAdminCheckout()->getQuote();
            } else {
                return $this->_getCheckout()->getQuote();
            }
        }
    }

    /**
     * Retrieves Order
     *
     * @param integer $incrementId
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder($incrementId) {
        return Mage::getModel('sales/order')->loadByIncrementId($incrementId);
    }
    
    
    
    public function getDiscount(){
	$discount = 0;
	$totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
	
	if(isset($totals['discount']) && $totals['discount']->getValue()) {
	    $discount =  $totals['discount']->getValue();
	}
	
	return $discount;
    }
    
    public function postPago(){ 
        
        //seta sdk php mercadopago
	$client_id = Mage::getStoreConfig('payment/mercadopago_configuration/client_id');
        $client_secret = Mage::getStoreConfig('payment/mercadopago_configuration/client_secret');

        $mp = new MP($client_id, $client_secret);
	
	//monta a preferencia
	$pref = $this->makePreference();
	
	//faz o post do pagamento
        $response = $mp->create_custon_payment($pref);

	if($response['status'] == 200 || $response['status'] == 201):
	    return $response;
	else:
	    $e = "";
	    foreach($response['response']['cause'] as $error):
		switch ($error['code']) {
		    case "106":
			$e .=  Mage::helper('mercadopago_transparent')->__('You can not make payments to users in other countries.');
			break;
		    
		    case "109":
			$e .=  Mage::helper('mercadopago_transparent')->__('Payment Method selected does not process payments in installments selected. Choose another card or another payment method.');
			break;
		    
		    case "126":
			$e .=  Mage::helper('mercadopago_transparent')->__('We could not process your payment. Error code: 126.');
			break;
		    
		    case "129":
			$e .=  Mage::helper('mercadopago_transparent')->__('Payment Method selected does not process payments for the selected amount. Choose another card or another payment method.');
			break;
		    
		    case "145":
			$e .=  Mage::helper('mercadopago_transparent')->__('We could not process your payment. Error code: 145.');
			break;
		    
		    case "150":
			$e .=  Mage::helper('mercadopago_transparent')->__('You can not make payments. Error code: 150.');
			break;

		    case "151":
			$e .=  Mage::helper('mercadopago_transparent')->__('You can not make payments.');
			break;
		    
		    case "160":
			$e .=  Mage::helper('mercadopago_transparent')->__('We could not process your payment. Error code: 160.');
			break;
		    
		    case "204":
			$e .=  Mage::helper('mercadopago_transparent')->__('Payment Method selected is not available at this time. Choose another card or another payment method.');
			break;
		    
		    case "801":
			$e .=  Mage::helper('mercadopago_transparent')->__('You made a similar payment moments ago. Try again in a few minutes.');
			break;
		    
		    default:
			$e .=  Mage::helper('mercadopago_transparent')->__("We could not process your payment. %s", $this->htmlEscape(json_encode($error)));
			break;
		}
		
	    endforeach;

	    Mage::throwException($e);
	    return false;
	endif;
        
    }
    
    function makePreference(){
	
	$quote = $this->_getQuote();
        $orderId = $quote->getReservedOrderId();
        $order = $this->_getOrder($orderId);
    
	
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $model = Mage::getModel('catalog/product');
    
         
        //pega payment dentro da order para pegar as informacoes adicionadas pela funcao assignData()
	$payment = $order->getPayment();
        
        //pega valor total da compra
        $item_price = $order->getBaseGrandTotal();
        if (!$item_price) {
            $item_price = $order->getBasePrice() + $order->getBaseShippingAmount();
        }
	
        //pega o valor total da compra somando o frete
        $item_price = number_format($item_price, 2, '.', '');
	
        //setta informaçnoes
        $arr = array();
        $arr['external_reference'] = $orderId;
        $arr['amount'] = (float) $item_price;
        $arr['reason'] = Mage::helper('mercadopago_transparent')->__("Order # %s in store %s", $orderId, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,true));
        //não é necessario settar currency_id, pois ja identifica no backend
	//$arr['currency_id'] = "BRL";
        $arr['installments'] = (int) $payment->getAdditionalInformation("installments");
        $arr['payment_method_id'] = $payment->getAdditionalInformation("payment_method");
        $arr['payer_email'] = htmlentities($customer->getEmail());
        
        if($payment->getAdditionalInformation("card_token_id") != ""){
	    $arr['card_token_id'] = $payment->getAdditionalInformation("card_token_id");
	}
	
	if($payment->getAdditionalInformation("issuers") != ""){
	    $arr['card_issuer_id'] = (int) $payment->getAdditionalInformation("issuers");
	}
	
        
        //monta array de produtos 
        $arr['items'] = array();
        foreach ($order->getAllVisibleItems() as $item) {

            $prod = $model->loadByAttribute('sku', $item->getSku());

            //get image
	    try{
		$imagem = $prod->getImageUrl();
	    }catch(Exception $e){
		$imagem = "";
	    }
            
            $arr['items'][] = array(
                "id" => $item->getSku(),
                "title" => $item->getName(),
                "description" => $item->getName(),
                "picture_url" => $imagem,
                "category_id" => Mage::getStoreConfig('payment/mercadopago_configuration/category_id'),
                "quantity" => (int) number_format($item->getQtyOrdered(), 0, '.', ''),
                "unit_price" => (float) number_format($prod->getPrice(), 2, '.', '')
            );
            
        }
	
	
	//verifica se existe desconto, caso exista adiciona como um item
	$discount = $this->getDiscount();
	if($discount != 0){
	    $arr['items'][] = array(
                "title" => "Discount by the Store",
                "description" => "Discount by the Store",
                "quantity" => (int) 1,
                "unit_price" => (float) number_format($discount, 2, '.', '')
            );
	}
	
        //pega dados de envio
        if(method_exists($order->getShippingAddress(), "getData")){
            $shipping = $order->getShippingAddress()->getData();
            $arr['shipments']['receiver_address'] = array(
		"floor" => "-",
		"zip_code" => $shipping['postcode'],
		"street_name" => $shipping['street'] . " - " . $shipping['city'] . " - " . $shipping['country_id'],
		"apartment" => "-",
		"street_number" => "0"
            );
            $arr['customer']['phone'] = array(
                "area_code" => "-",
                "number" => $shipping['telephone']
            );
        }
        
        //formata a data do usuario para o padrao do mercado pago YYYY-MM-DDTHH:MM:SS
        $date_creation_user = date('Y-m-d',$customer->getCreatedAtTimestamp()) . "T" . date('H:i:s',$customer->getCreatedAtTimestamp());
        
	//$quote = Mage::getSingleton('checkout/session')->getQuote();
	$billing_address = $quote->getBillingAddress();
	$billing_address = $billing_address->getData();
        
        //set informaçoes do usuario
        $arr['customer']['registration_date'] = $date_creation_user;
        $arr['customer']['email'] = htmlentities($customer->getEmail());
        $arr['customer']['first_name'] = htmlentities($customer->getFirstname());
        $arr['customer']['last_name'] = htmlentities($customer->getLastname());
        
        //set o documento do usuario
	if($payment['additional_information']['doc_number'] != ""){
	    $arr['customer']['identification'] = array(
		"type" => "CPF",
		"number" => $payment->getAdditionalInformation("doc_number")
	    );
	}
        
        //set endereco do usuario
        $arr['customer']['address'] = array(
            "zip_code" => $billing_address['postcode'],
            "street_name" => $billing_address['street'] . " - " . $billing_address['city'] . " - " . $billing_address['country_id'],
            "street_number" => "0"
        );
        
	//define a url de notificacao 
	$arr['notification_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,true) . "mercadopago_standard/notification";
	
	//pega o email e o nome do usuario guest
	if($arr['payer_email'] == "" && $arr['customer']['email'] == ""){
	    $arr['payer_email'] = $order['customer_email'];
	    $arr['customer']['email'] = $order['customer_email'];
	    $arr['customer']['first_name'] = $order->getBillingAddress()->getFirstname();
	    $arr['customer']['last_name'] = $order->getBillingAddress()->getLastname();
	}
	
	//adiciona a sponsor_id para as vendas serem identificadas
	//$arr['sponsor_id'] = "";
	
	return $arr;
	
    }
    
    public function getPaymentMethods(){

	$this->client_id = Mage::getStoreConfig('payment/mercadopago_configuration/client_id');
        $this->client_secret = Mage::getStoreConfig('payment/mercadopago_configuration/client_secret');
	
	$mp = new MP ($this->client_id, $this->client_secret);
	$access_token = $mp->get_access_token();

        $payment_methods = MPRestClient::get("/checkout/custom/payment_methods?access_token=" . $access_token);
	
	return $payment_methods;
    }  
}

?>