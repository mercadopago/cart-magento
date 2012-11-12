<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */


class Mpexpress_Block_Information_Success extends Mage_Core_Block_Template

{
    protected $_order = null;
    protected $_id = null;
    protected $_address = null;
    protected $_editpage = 'mpexpress/information/address/';
    protected $_home = '';

    protected function _beforeToHtml()
    {  
       $mpid = Mage::getSingleton('customer/session')->getMpCartId();
       $mpcart = Mage::getModel('mpexpress/mpcart')->load($mpid);
       $this->_id = $mpcart->getOrderId();
       $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->_id); 
       $this->_address = Mage::getModel('sales/order_address')->load($this->_order['shipping_address_id']);
       
       
       $billname =       $this->_order['customer_firstname'];
       $billlastname =   $this->_order['customer_lastname'];
       $billemail =      $this->_order['customer_email'];
       
       
       $orderid            = $this->_id;
       $status             = $this->_order['status'];
       $customername =       $this->_address['firstname'];
       $customerlastname =   $this->_address['lastname'];
       $customerstreet =     $this->_address['street'];
       $customercity =       $this->_address['city'];
       $customerphone =      $this->_address['telephone'];
       $customerState =      $this->_address['region'];
       $customerpostcode =   $this->_address['postcode'];
       $customercountry =    $this->_address['country_id'];
       
       $itens = $this->_order->getAllVisibleItems();
       $subtotal = Mage::helper('core')->currency($this->_order->getBaseSubtotal(),true,false);
       $shiptotal = Mage::helper('core')->currency($this->_order->getBaseShippingAmount(),true,false);
       $total = Mage::helper('core')->currency($this->_order->getBaseGrandTotal(),true,false);
       

       
       $this->setCustomername($customername)->setCustomerlastname($customerlastname)->setBillName($billname)->setBillLastname($billlastname)->setBillEmail($billemail)
            ->setCustomerstreet($customerstreet)->setCustomercity($customercity)->setCustomerphone($customerphone)
            ->setCustomerstate($customerState)->setCustomerpostcode($customerpostcode)->setItens($itens)->setTotal($total)->setCustomercountry($customercountry)
            ->setShipping($shiptotal)->setSubTotal($subtotal)->setStatus($status)->setOrderId($orderid)->setEditPage($this->getUrl($this->_editpage))->setHome($this->getUrl($this->_home));  
               ;
       
    }
    
    
    protected function _toHtml()
    {   
         
         return parent::_toHtml();
    }
    
    
    

}




?>
