<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) | Edited: Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */


class Mpexpress_Model_Express extends Mage_Payment_Model_Method_Abstract{   

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
    
  
    
    protected function _construct(){

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
        
        $quote = Mage::getSingleton('checkout/session')->getQuote();        

        foreach ($order->getAllVisibleItems() as $item) {
            //modificado por e-values para permitir el manejo de kits
            if (strpos($item->getSku(), '-') !== false) {
                $skus = explode("-", $item->getSku());
                $prod = $model->loadByAttribute('sku', $skus[0]);
            } else {
                $prod = $model->loadByAttribute('sku', $item->getSku());
            }
            $image[] = $prod->getImageUrl();
            $name .= $item->getName();
        }
        
        
        //Shipping
        $shipping = $order->getShippingAddress()->getData();
        $shipments = array(
            "receiver_address" => array(
            "floor" => "-",
            "zip_code" => $shipping['postcode'],
            "street_name" => $shipping['street'] . " - " . $shipping['city'] . " - " . $shipping['country_id'],
            "apartment" => "-",
            "street_number" => "-"
            )
        );
        
        //Force format YYYY-DD-MMTH:i:s
        $date_creation_user = date('Y-m-d',$customer->getCreatedAtTimestamp()) . "T" . date('H:i:s',$customer->getCreatedAtTimestamp());
        
        $billing_address = $order->getBillingAddress();
        $billing_address = $billing_address->getData();

        $payer = array(
            "name" => htmlentities($customer->getFirstname()),
            "surname" => htmlentities($customer->getLastname()),
            "email" => htmlentities($customer->getEmail()),
            "date_created" => $date_creation_user,
            "phone" => array(
                "area_code" => "-",
                "number" => $shipping['telephone']
            ),
            "address" => array(
                "zip_code" => $billing_address['postcode'],
                "street_name" => $billing_address['street'] . " - " . $billing_address['city'] . " - " . $billing_address['country_id'],
                "street_number" => "-"
            ),
            "identification" => array(
                "number" => "null",
                "type" => "null"
            )
        );
        
        //items
        $item_price = $order->getBaseGrandTotal();
        if (!$item_price) {
            $item_price = $order->getBasePrice() + $order->getBaseShippingAmount();
        }
        
        $item_price = number_format($item_price, 2, '.', '');
        $items = array(
            array (
            "id" => $orderIncrementId,
            "title" => utf8_encode($name),
            "description" => utf8_encode($name),
            "quantity" => 1,
            "unit_price" => round($item_price, 2),
            "currency_id" => $this->getConfigData('currency'),
            "picture_url"=> $image[0],
            "category_id"=> $this->getConfigData('category_id')
            )
        );
        
        $installments = (int)$this->getConfigData('installments');
        
        //send null installment case send 0 or empty
        if($installments == 0 || $installments == ""):
            $installments = null;
        endif;
        
        
        //payment_methods
        $exclude = $this->getConfigData('excluded_payment_methods');
        if($exclude != ''):
        //case exist exclude methods
            $excludemethods = array();
            $methods_excludes = preg_split("/[\s,]+/", $excludes); 
            foreach ($methods_excludes as $exclude ){
                $excludemethods[] = array('id' => $exclude);     
            }
        
            $payment_methods = array(
                "installments" => $installments,
                "excluded_payment_methods" => $excludemethods
            );
        else:
            //case not exist exclude methods
            $payment_methods = array(
                "installments" => $installments
            );
        endif;
        
        
        //set back url
        $back_urls = array(
            "pending" => $this->getConfigData('url_success'),
            "success" => $this->getConfigData('url_process')
        );
        
        
        //mount array pref
        $pref = array();
        $pref['external_reference'] = 'mpexpress-' . $orderIncrementId;
        $pref['payer'] = $payer;
        $pref['shipments'] = $shipments;
        $pref['items'] = $items;
        $pref['back_urls'] = $back_urls;
        $pref['payment_methods'] = $payment_methods;
        
        $sandbox = $this->getConfigData('sandbox_checkout') == 1 ? true: false;
        
        return Mage::getModel('mpexpress/checkout')->GetCheckout($pref, $sandbox);
    
  }
    
}

?>
