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

class MercadoPago_MPexpress_Block_General_Botao extends Mage_Core_Block_Template
{
    protected $_shouldRender = true;
    protected $_checkoutpage = 'mpexpress/checkout/zipcode';
     
 
    protected function _beforeToHtml()
    {  
       $country = Mage::getModel('mpexpress/express')->getConfigData('acc_origin');
       $cart = Mage::getModel('mpexpress/express')->getConfigData('express_button_checkout');
       $side = Mage::getModel('mpexpress/express')->getConfigData('express_button_checkout_sidebar');
       $prod = Mage::getModel('mpexpress/express')->getConfigData('express_button_product');   
       $this->setcheckoutexpress($this->getUrl($this->_checkoutpage))->setCountry($country)
       ->setAllowedProduct($prod)->setAllowedCart($cart)->setAllowedSidebar($side)        
       ->setimgcheckoutBr($this->getSkinUrl('images/mpexpress/pagarbr.jpg'))->setimgcheckoutAr($this->getSkinUrl('images/mpexpress/pagarar.jpg'));  
    }
  
    protected function _toHtml()
    {
         return parent::_toHtml();
    }
    
   

}

?>