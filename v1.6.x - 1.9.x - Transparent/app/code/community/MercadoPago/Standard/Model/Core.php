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

class MercadoPago_Standard_Model_Core extends Mage_Payment_Model_Method_Abstract{
    
    
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
    
    
	public function log($message, $file = "mercadopago.log", $array = null){
	
		//pega a configuração de log no admin, essa variavel vem como true por padrão
		$action_log = Mage::getStoreConfig('payment/mercadopago_configuration/logs');
		
		//caso tenha um array, transforma em json para melhor visualização
		if(!is_null($array))
		$message .= " - " . json_encode($array);
		
		//set log
		Mage::log($message, null, $file, $action_log);
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
