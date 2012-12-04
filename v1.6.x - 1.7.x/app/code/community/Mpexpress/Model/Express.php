<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */


class Mpexpress_Model_Express extends Mage_Payment_Model_Method_Abstract
{   
    
   

    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';
    protected $_formBlockType = 'mpexpress/checkout_list';
    protected $_code = 'mpexpress';
    
    protected $_isGateway                   = true;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = true;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = true;
    protected $_canFetchTransactionInfo     = true;
    protected $_canCreateBillingAgreement   = true;
    protected $_canReviewPayment            = true;
    
  
    
    protected function _construct()
    {
        $this->_init('mpexpress/express');
    }
    
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('mpexpress/checkout/redirect', array('_secure' => true));
    }
      
    
    public function getInitPoint() {
        
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $name = '#' . $orderIncrementId . ' - '; 
        $model = Mage::getModel('catalog/product');
        foreach ($order->getAllVisibleItems() as $item) {
            $prod = $model->loadByAttribute('sku', $item->getSku()); 
            $image[] = $prod->getImageUrl();
            $name .= $item->getName();
        }
        $item_price = $order->getBaseGrandTotal();

        if (!$item_price) {
            $item_price = $order->getBasePrice() + $order->getBaseShippingAmount();
        }
        $item_price = number_format($item_price, 2, '.', '');
        
        
        //
        $data = array(
            'external_reference' => 'mpexpress-'.$orderIncrementId ,
            'id_item'=> $order,
            'title' =>utf8_encode($name),
            'quantity' => 1,
            'title' =>  $name,
            'amount' => $item_price,
            'currency' => $this->getConfigData('currency'),
            'image' => $image[0],
            'payment_firstname' => htmlentities($customer->getFirstname()),
            'payment_lastname' => htmlentities($customer->getLastname()),
            'email' => htmlentities($customer->getEmail()),
            'pending' => $this->getConfigData('url_success'),
            'approved' => $this->getConfigData('url_process'),
            'installments' => (int)$this->getConfigData('installments'),
        );


        $exclude = $this->getConfigData('excluded_payment_methods');
        return Mage::getModel('mpexpress/checkout')->GetCheckout($data,$exclude);
        
      }
      
      

    
    
    
    
    
}







?>
