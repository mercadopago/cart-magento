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


class MercadoPago_Block_Customticket_Form extends Mage_Payment_Block_Form_Cc{
    
    protected function _construct(){
        
        parent::_construct();
        
        $this->setTemplate('mercadopago/custom_ticket/form.phtml');
        
    }
    
    protected function _prepareLayout(){
    
        //pega public key para settar no aquivo mercadopago.js
        $model = Mage::getModel('mercadopago/custom_payment');
        $public_key = $model->getConfigData('public_key');
    
        //init js no header
        $block = Mage::app()->getLayout()->createBlock('core/text', 'js_mercadopago');
        $block->setText(
            sprintf(
                '
                <script type="text/javascript">var PublicKeyMercadoPagoTransparent = "' . $public_key .'";</script>
                <script type="text/javascript" src="%s"></script>',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/mercadopago.js'
            )
        );
        
        $head = Mage::app()->getLayout()->getBlock('after_body_start');

        if($head){
            $head->append($block);
        }
        
        return parent::_prepareLayout();
    }
    
    function getTicketsOptions(){
        $payment_methods = Mage::getModel('mercadopago/core')->getPaymentMethods();
        $tickets = array();
        
        //percorre todos os payments methods
        foreach($payment_methods['response'] as $pm){
            
            //filtra por tickets
            if($pm['payment_type_id'] == "ticket"){
                $tickets[] = $pm;
            }
        }
        
        return $tickets;
    }
}