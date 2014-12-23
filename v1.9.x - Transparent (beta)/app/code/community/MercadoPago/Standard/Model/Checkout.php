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

class MercadoPago_Standard_Model_Checkout extends Mage_Payment_Model_Method_Abstract{
    
    //configura o lugar do arquivo para listar meios de pagamento
    protected $_formBlockType = 'mercadopago_standard/form';
    protected $_infoBlockType = 'mercadopago_standard/info';
    
    protected $_code = 'mercadopago_standard';

    protected $_isGateway                   = true;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = true;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = true;
    protected $_canFetchTransactionInfo     = true;
    protected $_canCreateBillingAgreement   = true;
    protected $_canReviewPayment            = true;

    protected function _construct(){
        $this->_init('mercadopago_standard/checkout');
    }
    
    public function postPago(){ 
        //seta sdk php mercadopago
        $client_id = Mage::getStoreConfig('payment/mercadopago_configuration/client_id');
        $client_secret = Mage::getStoreConfig('payment/mercadopago_configuration/client_secret');
        $mp = new MP($client_id, $client_secret);
	
        //monta a prefernecia
	$pref = $this->makePreference();
        
        //faz o posto do pagamento
        return $mp->create_preference($pref);
    }
    
    public function getOrderPlaceRedirectUrl() {
        
        // requisicao vem da pagina de finalizacao de pedido
        return Mage::getUrl('mercadopago_standard/pay', array('_secure' => true));
    
    }
    
    public function getDiscount($order){
	$discount = 0;
	
        $order = $order->getData();
	
	if(isset($order['base_discount_amount']) && $order['base_discount_amount'] < 0) {
	    $discount =  $order['base_discount_amount'];
	}
	
	return $discount;
    }
    
    function makePreference(){
	
	//pega a order atual
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $model = Mage::getModel('catalog/product');
    
        
	
        //pega payment dentro da order para pegar as informacoes adicionadas pela funcao assignData()
	$payment = $order->getPayment();
	
	//init array preferneces
	$arr = array();
	
	//seta o external_reference para concilia‹o futura
        $arr['external_reference'] = $orderIncrementId;
       
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
	$discount = $this->getDiscount($order);
	
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
            $arr['payer']['phone'] = array(
                "area_code" => "-",
                "number" => $shipping['telephone']
            );
        }
        
	//adiciona o valor do frete nas preferencias
	if($order->getBaseShippingAmount() != "" && $order->getBaseShippingAmount() > 0){
	    $arr['shipments']['cost'] = (float) $order->getBaseShippingAmount();
	}
	
        
        //pega informaoes de cadastro do usuario
        $billing_address = $order->getBillingAddress();
        $billing_address = $billing_address->getData();
        
        //formata a data do usuario para o padrao do mercado pago YYYY-MM-DDTHH:MM:SS
        $arr['payer']['date_created'] = date('Y-m-d',$customer->getCreatedAtTimestamp()) . "T" . date('H:i:s',$customer->getCreatedAtTimestamp());
        
        //set informaoes do usuario
        $arr['payer']['email'] = htmlentities($customer->getEmail());
        $arr['payer']['first_name'] = htmlentities($customer->getFirstname());
        $arr['payer']['last_name'] = htmlentities($customer->getLastname());
        
        //set o documento do usuario
	if(isset($payment['additional_information']['doc_number']) && $payment['additional_information']['doc_number'] != ""){
	    $arr['payer']['identification'] = array(
		"type" => "CPF",
		"number" => $payment['additional_information']['doc_number']
	    );
	}
        
        //set endereco do usuario
        $arr['payer']['address'] = array(
            "zip_code" => $billing_address['postcode'],
            "street_name" => $billing_address['street'] . " - " . $billing_address['city'] . " - " . $billing_address['country_id'],
            "street_number" => "0"
        );
        
        //setta as urls de retorno
        $arr['back_urls'] = array(
            "success" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,true) . "checkout/onepage/success",
            "pending" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,true) . "checkout/onepage/success",
            "failure" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,true) . "checkout/onepage/success"
        );
        
	//define a url de notificacao 
	$arr['notification_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,true) . "mercadopago_standard/notification";
	
	//pega o email e o nome do usuario guest
	if($arr['payer']['email'] == ""){
	    $arr['payer']['email'] = $order['customer_email'];
	    $arr['payer']['first_name'] = $order->getBillingAddress()->getFirstname();
	    $arr['payer']['last_name'] = $order->getBillingAddress()->getLastname();
	}
        
        // pega os meios de pagamento que ele dejexa excluir
        $checkout = Mage::getModel('mercadopago_standard/checkout');
        $excluded_payment_methods = $checkout->getConfigData('excluded_payment_methods');
        $arr_epm = explode(",", $excluded_payment_methods);
        if(count($arr_epm) > 0){
            $arr['payment_methods']['excluded_payment_methods'] = array();
            
            foreach($arr_epm as $m):
                $arr['payment_methods']['excluded_payment_methods'][] = array("id" => $m);
            endforeach;
            
        }
        
        //seta o numero de parcelas maxima aceita pelo lojista
        $installments = $checkout->getConfigData('installments');
        $arr['payment_methods']['installments'] = (int) $installments;
        
	
        //define o retorno automatico ao finalizar o checkout
        $auto_return = $checkout->getConfigData('auto_return');
        if($auto_return == 1){
            $arr['auto_return'] = "approved";
        }
	
	//adiciona o sponsor_id para as vendas serem identificadas
	//$arr['sponsor_id'] = "";
	
	return $arr;
	
    }
    
    
    public function getPayment($payment_id){
	$model = $this;
	$this->client_id = Mage::getStoreConfig('payment/mercadopago_configuration/client_id');
        $this->client_secret = Mage::getStoreConfig('payment/mercadopago_configuration/client_secret');
        $mp = new MP($this->client_id, $this->client_secret);
	return $mp->get_payment($payment_id);
    }
    
    public function getMerchantOrder($merchant_order_id){
	$model = $this;
	$this->client_id = Mage::getStoreConfig('payment/mercadopago_configuration/client_id');
	$this->client_secret = Mage::getStoreConfig('payment/mercadopago_configuration/client_secret');
        $mp = new MP($this->client_id, $this->client_secret);
	return $mp->get_merchant_order($merchant_order_id);
    }
}

?>
