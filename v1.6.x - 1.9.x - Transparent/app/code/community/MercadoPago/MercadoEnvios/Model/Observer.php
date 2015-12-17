<?php

class MercadoPago_MercadoEnvios_Model_Observer
{

    protected $_useMercadoEnvios;

    public function filterActivePaymentMethods($observer)
    {
        if ($this->_useMercadoEnvios()) {
            $event = $observer->getEvent();
            $method = $event->getMethodInstance();
            $result = $event->getResult();
            if ($method->getCode() != 'mercadopago_standard') {
                $result->isAvailable = false;
            }
        }
    }

    protected function _useMercadoEnvios()
    {
        if (empty($this->_useMercadoEnvios)) {
            $quote = Mage::helper('mercadopago_mercadoenvios')->getQuote();
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            $this->_useMercadoEnvios = Mage::helper('mercadopago_mercadoenvios')->isMercadoEnviosMethod($shippingMethod);
        }

        return $this->_useMercadoEnvios;
    }

    public function checkAndValidateData()
    {
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        $code = Mage::getModel('mercadopago/source_country')->getCodeByValue($country);
        Mage::getConfig()->saveConfig('carriers/mercadoenvios/specificcountry', $code);
    }
}