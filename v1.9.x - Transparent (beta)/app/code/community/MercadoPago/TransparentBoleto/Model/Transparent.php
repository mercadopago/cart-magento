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
    
    protected $_code = 'mercadopago_transparentboleto';

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
        $this->_init('mercadopago_transparentboleto/transparent');
    }
    
    public function assignData($data){
        
        // route /checkout/onepage/savePayment
        if(!($data instanceof Varien_Object)){
            $data = new Varien_Object($data);
        }
        
        //get array info
        $info_form = $data->getData();
        
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('card_token_id',$info_form['card_token_id']);
        $info->setAdditionalInformation('payment_method',$info_form['payment_method_boleto']);
        $info->setAdditionalInformation('installments',$info_form['installments']);
        $info->setAdditionalInformation('doc_number',$info_form['doc_number']);
        
        
        return $this;
    }
    
    public function getOrderPlaceRedirectUrl() {
        
        // requisicao vem da pagina de finalizacao de pedido
        return Mage::getUrl('mercadopago_transparent/pay', array('_secure' => true));
    
    }
    
}

?>
