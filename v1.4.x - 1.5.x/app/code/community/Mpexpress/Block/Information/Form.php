<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */


class Mpexpress_Block_Information_Form extends Mage_Core_Block_Template

{
    protected $_postpage = 'mpexpress/information/post';
    protected $_request = null;
    protected $_id = null;
    protected $_order = null;
    protected $_lastship = null;
    protected $_cepachage = null;
    
    protected function _beforeToHtml()
    {  

        
   //  $countryName = Mage::getModel('directory/country')->getName();//get country name
    
       $this->_request = $this->getRequest()->getParams(); 
       $this->_id = Mage::getSingleton('customer/session')->getOrderId();
       $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->_id); 
       $this->_address = Mage::getModel('sales/order_address')->load($this->_order['shipping_address_id']);
       $this->_lastship = Mage::getSingleton('customer/session')->getLastShip($ship);
       $this->_cepachage =  Mage::getModel('mpexpress/express')->getConfigData('change_postalcode');
       $this->setpostpage($this->getUrl($this->_postpage))->setId($this->_id)->setOrder($this->_order)->setAddress($this->_address)->setLastShip($this->_lastship)->setCepChange($this->_cepachage);
       $this->ClearCart();
    }
    
    
    protected function _toHtml()
    {   
         
         return parent::_toHtml();
    }
    
    protected function ClearCart()
    {
    $cart = Mage::getSingleton('checkout/cart');
    foreach( Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item ){
    $cart->removeItem( $item->getId() );
    }
    $cart->save();
    }

    
    
    

}




?>
