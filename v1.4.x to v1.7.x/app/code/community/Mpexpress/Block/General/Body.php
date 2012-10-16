<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright   Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */



// class MPexpress_Block_Template_Bt extends Mage_Core_Block_Template

class MPexpress_Block_General_Body extends Mage_Core_Block_Template
{

    protected $_checkoutpage = 'mpexpress/checkout/zipcode';
 
    protected function _beforeToHtml()
    {  
     $this->setcheckoutexpress($this->getUrl($this->_checkoutpage));   
   }
  
    protected function _toHtml()
    {
         return parent::_toHtml();
    }
    
   

}

?>