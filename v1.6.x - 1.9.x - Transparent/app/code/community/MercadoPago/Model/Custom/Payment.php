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

class MercadoPago_Model_Custom_Payment extends Mage_Payment_Model_Method_Abstract{
    
    //configura o block do formulario e de informações sobre o pagamento
    protected $_formBlockType = 'mercadopago/custom_form';
    protected $_infoBlockType = 'mercadopago/custom_info';
    
    protected $_code = 'mercadopago_custom';
    
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
			Mage::throwException(Mage::helper('mercadopago')->__('Verify the form data or wait until the validation of the payment data'));
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
		
		if(isset($info_form['coupon_code'])){
			$info->setAdditionalInformation('coupon_code', $info_form['coupon_code']);
		}
		
		if($info_form['card_token_id'] != ""):
			$info->setAdditionalInformation('expiration_date', $info_form['cardExpirationMonth'] . "/" . $info_form['cardExpirationYear']);
			$info->setAdditionalInformation('cardholderName', $info_form['cardholderName']);
			$info->setAdditionalInformation('trunc_card', $info_form['trunc_card']);
		endif;
	
        
        //caso seja não tenha card_token_id
        /*if($info_form['card_token_id'] == ""):
			Mage::throwException(Mage::helper('mercadopago')->__('Verify the form data or wait until the validation of the payment data'));
			return false;
		endif;*/
	
	
        return $this;
    }
    
	public function getOrderPlaceRedirectUrl() {
		// requisicao vem da pagina de finalizacao de pedido
		return Mage::getUrl('mercadopago/success', array('_secure' => true));
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
		Mage::helper('mercadopago')->log("init post pago", 'mercadopago-custom.log');
        $core = Mage::getModel('mercadopago/core');
		
        //seta sdk php mercadopago
		$client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
        $client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
		
        $mp = new MP($client_id, $client_secret);
		
		//monta a preferencia
		$pref = $this->makePreference();
		Mage::helper('mercadopago')->log("make array", 'mercadopago-custom.log', $pref);
		
		//faz o post do pagamento
		$response = $mp->create_custon_payment($pref);
		Mage::helper('mercadopago')->log("post pago", 'mercadopago-custom.log', $response);
	
		if($response['status'] == 200 || $response['status'] == 201):
			return $response;
		else:
			$e = "";
			foreach($response['response']['cause'] as $error):
				switch ($error['code']) {
					case "106":
						$e .=  Mage::helper('mercadopago')->__('You can not make payments to users in other countries.');
						break;
					
					case "109":
						$e .=  Mage::helper('mercadopago')->__('Payment Method selected does not process payments in installments selected. Choose another card or another payment method.');
						break;
					
					case "126":
						$e .=  Mage::helper('mercadopago')->__('We could not process your payment. Error code: 126.');
						break;
					
					case "129":
						$e .=  Mage::helper('mercadopago')->__('Payment Method selected does not process payments for the selected amount. Choose another card or another payment method.');
						break;
					
					case "137":
						$e .=  Mage::helper('mercadopago')->__('The amount is required.');
						break;
					
					case "145":
						$e .=  Mage::helper('mercadopago')->__('We could not process your payment. Error code: 145.');
						break;
					
					case "150":
						$e .=  Mage::helper('mercadopago')->__('You can not make payments. Error code: 150.');
						break;
		
					case "151":
						$e .=  Mage::helper('mercadopago')->__('You can not make payments.');
						break;
					
					case "160":
						$e .=  Mage::helper('mercadopago')->__('We could not process your payment. Error code: 160.');
						break;
					
					case "204":
						$e .=  Mage::helper('mercadopago')->__('Payment Method selected is not available at this time. Choose another card or another payment method.');
						break;
					
					case "801":
						$e .=  Mage::helper('mercadopago')->__('You made a similar payment moments ago. Try again in a few minutes.');
						break;
					
					//validacao do coupon
					case "campaign_code_doesnt_match":
						$e .=  Mage::helper('mercadopago')->__("Doesn't find a campaign with the given code.");
						break;
					
					default:
						$e .=  Mage::helper('mercadopago')->__("We could not process your payment. %s", json_encode($response));
						break;
				}
			
			endforeach;
			Mage::helper('mercadopago')->log("erro post pago: " . $e, 'mercadopago-custom.log');
			Mage::helper('mercadopago')->log("response post pago: ", 'mercadopago-custom.log', $response);
			Mage::throwException($e);
			return false;
		endif;
        
    }
    
    function makePreference(){
		
		$core = Mage::getModel('mercadopago/core');
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
        $arr['reason'] = Mage::helper('mercadopago')->__("Order # %s in store %s", $orderId, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,true));
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

            $produto = $item->getProduct();

            //get image
			try{
				$imagem = $produto->getImageUrl();
			}catch(Exception $e){
				$imagem = "";
			}
            
            $arr['items'][] = array(
                "id" => $item->getSku(),
                "title" => $produto->getName(),
                "description" => $produto->getName(),
                "picture_url" => $imagem,
                "category_id" => Mage::getStoreConfig('payment/mercadopago/category_id'),
                "quantity" => (int) number_format($item->getQtyOrdered(), 0, '.', ''),
                "unit_price" => (float) number_format($produto->getPrice(), 2, '.', '')
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
		$arr['notification_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,true) . "mercadopago/notifications?checkout=custom";
		
		//pega o email e o nome do usuario guest
		if($arr['payer_email'] == "" && $arr['customer']['email'] == ""){
			$arr['payer_email'] = $order['customer_email'];
			$arr['customer']['email'] = $order['customer_email'];
			$arr['customer']['first_name'] = $order->getBillingAddress()->getFirstname();
			$arr['customer']['last_name'] = $order->getBillingAddress()->getLastname();
		}
		
		
		if($payment->getAdditionalInformation("coupon_code") != ""){
			
			$coupon_code = $payment->getAdditionalInformation("coupon_code");
			Mage::helper('mercadopago')->log("Validating coupon_code: " . $coupon_code, 'mercadopago-custom.log');
			
			$coupon = $core->validCoupon($coupon_code);
			Mage::helper('mercadopago')->log("Response API Coupon: " , 'mercadopago-custom.log', $coupon);
			
			if(	$coupon['status'] != 200){
				if(	$coupon['response']['error'] != "campaign-code-doesnt-match" &&
					$coupon['response']['error'] != "amount-doesnt-match" &&
					$coupon['response']['error'] != "transaction_amount_invalid"){
				
					// caso não seja os erros mapeados acima (todos são informandos no formulario no momento que aplica os desconto)
					// o coupon code é inserido no array para o post de pagamento
					// caso de erro significa que o coupon não é mais valido para utilização
					// pode ter ocorrido do usuario ja ter utilizado o coupon e mesmo assim prosseguir com o pagamento
					
					//adiciona o coupon amount, caso o usuario esteja passando pela v1
					$arr['coupon_amount'] = (float) $coupon['response']['coupon_amount'];
					$arr['coupon_code'] = $coupon_code;
					Mage::helper('mercadopago')->log("Coupon applied. API response 400, error not mapped", 'mercadopago-custom.log');	
				}else{
					Mage::helper('mercadopago')->log("Coupon invalid, not applied.", 'mercadopago-custom.log');	
				}
			}else{
				//adiciona o coupon amount, caso o usuario esteja passando pela v1
				$arr['coupon_amount'] = (float) $coupon['response']['coupon_amount'];
				$arr['coupon_code'] = $coupon_code;
				Mage::helper('mercadopago')->log("Coupon applied. API response 200.", 'mercadopago-custom.log');
				
			}
		}
		
		//verifico se o sponsor é diferente de null (se existe)
		$sponsor_id = Mage::getStoreConfig('payment/mercadopago/sponsor_id');
		Mage::helper('mercadopago')->log("Sponsor_id", 'mercadopago-standard.log', $sponsor_id);
		if($sponsor_id != null && $sponsor_id != ""){
			Mage::helper('mercadopago')->log("Sponsor_id identificado", 'mercadopago-custom.log', $sponsor_id);
			$arr['sponsor_id'] = (int) $sponsor_id;
		}
		
		return $arr;
	
    }
}
