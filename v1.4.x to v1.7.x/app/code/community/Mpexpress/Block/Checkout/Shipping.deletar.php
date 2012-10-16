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

class MPexpress_Block_Checkout_Shipping extends Mage_Core_Block_Template
{
 
   
     
    public function _construct()
    {
    
  //    $this->postshipping();
    }
    protected function _beforeToHtml()
    {
      
       //  $this->shpping();

    }
  
    protected function _toHtml()
    {
         return parent::_toHtml();
    }
    
    protected function shipping()
    { 
 

    }
    
    public function postShippingAction(){
        echo 'adas';die;
        $cep = $this->getRequest()->getParam('estimate_postcode');
        $cep = '06543210';
        $express = Mage::getModel('mpexpress/Express');      
        $acc_orign = $express->getConfigData('acc_origin');
        $acc_orign = 'MLB';
        
        switch ($acc_orign):
        case 'MLB':
        $country = 'BR';
        break;
        case 'MLA':
        $country = 'AR';    
        break;
        defaul:
        $country = 'BR';
        break;
        endswitch;
        var_dump($country);die;
      $cart = Mage::getSingleton('checkout/cart');
      $cart->init();
      $cart->save();
       

      
      $quote = $cart->getQuote()->getShippingAddress();
      
      $quote->setCity('')
             ->setCountryId($country)
             ->setPostcode($cep)
             ->setRegionId('0')
             ->setRegion('')
             ->setCollectShippingRates(True);
             $quote->save();
      $quote->setCartWasUpdated(true); 
      
      
      $methods = $quote->getQuote()->getShippingAddress()->getAllShippingRates();

       var_dump($methods);die;
       $this->SetMethods($methods);   
        
    }
    
    
    
    
    
    
   

}

?>