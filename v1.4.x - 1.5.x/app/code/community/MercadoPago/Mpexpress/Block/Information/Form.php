<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */


class MercadoPago_Mpexpress_Block_Information_Form extends Mage_Core_Block_Template

{
    protected $_postpage = 'mpexpress/information/post';
    protected $_request = null;
    protected $_id = null;
    protected $_order = null;
    protected $_lastship = null;
    protected $_cepachage = null;
    protected $_postalcode = null;
    protected $_orderId = null;
    protected $_address = null;

    
    protected function _beforeToHtml()
    {  
       
   //  $countryName = Mage::getModel('directory/country')->getName();//get country name
    
       $this->_request = $this->getRequest()->getParams(); 
       $this->_id = Mage::getSingleton('customer/session')->getMpCartId();
       $this->_mpcart = Mage::getModel('mpexpress/mpcart')->load($this->_id);
       $this->_orderId = $this->_mpcart->getOrderId();
       $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->_orderId);
       $this->_postalcode =  $this->_mpcart->getPostalCode();
       $this->_address = Mage::getModel('sales/order_address')->load($this->_order['shipping_address_id']);
       $this->_cepachage =  Mage::getModel('mpexpress/express')->getConfigData('change_postalcode');
       $this->_lastship = Mage::getSingleton('customer/session')->getLastShip();
       $this->setpostpage($this->getUrl($this->_postpage))->setId($this->_orderId)->setOrder($this->_order)->setAddress($this->_address)->setLastShip($this->_lastship)->setCepChange($this->_cepachage)->setPostalCode($this->_postalcode);
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
