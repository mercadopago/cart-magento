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

class MercadoPago_MPexpress_Block_General_Error extends Mage_Core_Block_Template
{

     protected function _beforeToHtml()
    {  
      $mens = Mage::getSingleton('checkout/session')->getMessages();
      $smessages = $mens->getErrors();
      $output = NULL;
      foreach ($smessages as $smessage) {
          $output .= $smessage->getText();        
     }   
      $this->setError($output); 
      
    }
    
    protected function _toHtml()
    {   
         $html = parent::_toHtml();
         return $html;
         
    }
    
  
    

}

?>