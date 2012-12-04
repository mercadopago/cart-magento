<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */


// class MPexpress_Block_Template_Bt extends Mage_Core_Block_Template

class MPexpress_Block_General_Head extends Mage_Core_Block_Template
{

 
    protected function _beforeToHtml()
    {  
     
    $checkouta = Mage::getModel('mpexpress/express')->getConfigData('express_button_checkout');
    $checkoutp = Mage::getModel('mpexpress/express')->getConfigData('express_button_product');
    $checkouts = Mage::getModel('mpexpress/express')->getConfigData('express_button_checkout_sidebar');
     
     if ($checkouta == 1 || $checkoutp == 1 ||$checkouts == 1){
         $this->setExpress(true);
     } else {
         $this->setExpress(false);
     }
        
   }
  
    protected function _toHtml()
    {
         return parent::_toHtml();
    }
    
   

}

?>