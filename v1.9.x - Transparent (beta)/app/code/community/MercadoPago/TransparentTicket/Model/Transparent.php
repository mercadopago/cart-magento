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

class MercadoPago_TransparentTicket_Model_Transparent extends Mage_Payment_Model_Method_Abstract{
    
    //configura o lugar do arquivo para listar meios de pagamento
    protected $_formBlockType = 'mercadopago_transparentticket/form';
    protected $_infoBlockType = 'mercadopago_transparentticket/info';
    
    protected $_code = 'mercadopago_transparentticket';

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
        //chama model para fazer o post do pagamento
	$response = Mage::getModel('mercadopago_transparent/transparent')->postPago();
	
        if($response !== false):
            $this->getInfoInstance()->setAdditionalInformation('activation_uri', $response['response']['activation_uri']);
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
	$info->setAdditionalInformation('payment_type_id', "ticket");
        $info->setAdditionalInformation('payment_method', $info_form['payment_method_boleto']);
        $info->setAdditionalInformation('card_token_id', "");
	$info->setAdditionalInformation('installments', 1);
        $info->setAdditionalInformation('doc_number', "");
        
        return $this;
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
