<?php

/**
 * Created by PhpStorm.
 * User: imasson
 * Date: 10/27/15
 * Time: 4:22 PM
 */
class MercadoPago_MercadoEnvios_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
            $quote = Mage::getModel('checkout/cart')->getQuote();
        }

        return $quote;
    }

    public function isMercadoEnviosMethod($method)
    {
        $shippingMethod = substr($method,0,strpos($method,'_'));
        return ($shippingMethod == MercadoPago_MercadoEnvios_Model_Shipping_Carrier_MercadoEnvios::CODE);
    }
}