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

class MercadoPago_SuccessController extends Mage_Core_Controller_Front_Action{
    
    public function indexAction(){
        
        $this->loadLayout();
        
        //instancia block de success
        $block = Mage::app()->getLayout()->createBlock('mercadopago/success');
        
        //insere o block
        $this->getLayout()->getBlock('content')->append($block);
        $this->_initLayoutMessages('core/session');
        
        //adiciona uma clean page 
        $root = $this->getLayout()->getBlock('root');
        $root->setTemplate("mercadopago/clean.phtml");
                    
        $this->renderLayout();
    }
    
}
