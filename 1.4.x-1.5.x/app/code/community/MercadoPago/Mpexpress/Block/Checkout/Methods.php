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

class MercadoPago_MPexpress_Block_Checkout_Methods extends Mage_Core_Block_Template {

    public $_rates;
    public $_car;
    protected $_postpage = 'mpexpress/checkout/shippingUpdate';
    protected $_ceppage = 'mpexpress/checkout/zipcode';
 

    public function _construct() {

        $this->_car = Mage::getSingleton('checkout/cart');
        //   $this->_car->init();
        $this->_car->save();
        
        
        
        //       $this->_car->getQuote()->getShippingAddress()->collectShippingRates()->save();
    }

    protected function _beforeToHtml() {
        $askcep = Mage::getModel('mpexpress/Express')->getConfigData('ask_postalcode');
        $this->setAskcep($askcep);
        $this->setpostpage($this->getUrl($this->_postpage));
        $this->setceppage($this->getUrl($this->_ceppage));
    }

    protected function _toHtml() {
        return parent::_toHtml();
    }

    protected function getShippingRates() {


        $methods = $this->_car->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
        // If has more than one method, shows all methods
        if (count($methods) > 1) {
            $this->_car = Mage::getSingleton('checkout/cart');
            $this->_rates = $methods;
            return $this->_rates;
            
        // if has only one method
        } elseif (count($methods) == 1){
            foreach ($methods as $method ) {
                $code[] = $method;  
            }
            
            $error = 0;
            foreach ($code[0] as $_rate) {
                   $codava[] = $_rate->getCode();
                   if($_rate->getErrorMessage()){
                   $error += 1;
                   }
            }
            
                    // if has more than one submethod, list all submethod
                    if (count($codava)>1){
                        $this->_car = Mage::getSingleton('checkout/cart');
                        $this->_rates = $methods;
                        return $this->_rates;
                    // if has error, show error
                        
                    } elseif($error >= 1) {
                        $this->_car = Mage::getSingleton('checkout/cart');
                        $this->_rates = $methods;
                        return $this->_rates;
                    } else {
 
                     // if has also only one submethod , jump to the next steap
                    $this->_car->getQuote()->getShippingAddress()
                                           ->setShippingMethod($codava[0])
                                           ->setCartWasUpdated(true)
                                           ->collectShippingRates()
                                           ->collectTotals()
                                           ->save();
                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('mpexpress/checkout/cart'));
                   }
            
            
        } else {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('mpexpress/checkout/noMethod'));
        }
    }

    public function getAddress() {
        if (empty($this->_address)) {
            $this->_address = $this->_car->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    public function getCarrierName($carrierCode) {
        if ($name = Mage::getStoreConfig('carriers/' . $carrierCode . '/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function getAddressShippingMethod() {
        return $this->_car->getQuote()->getShippingMethod();
    }

    public function getShippingPrice($price, $flag) {
        return $this->_car->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }

}

?>