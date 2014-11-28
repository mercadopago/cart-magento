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


class MercadoPago_TransparentBoleto_Model_Transparent extends Mage_Payment_Model_Method_Abstract{
    
    //configura o lugar do arquivo para listar meios de pagamento
    protected $_formBlockType = 'mercadopago_transparentboleto/form';
    protected $_infoBlockType = 'mercadopago_transparentboleto/info';
    
    protected $_code = 'mercadopago_transparentboleto';

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
        $info->setAdditionalInformation('payment_method', $info_form['payment_method_boleto']);
        $info->setAdditionalInformation('card_token_id', "");
	$info->setAdditionalInformation('installments', 1);
        $info->setAdditionalInformation('doc_number', "");
        
        
        return $this;
    }
    
}

?>
