<?php

class MercadoPago_Core_Block_Calculator_CalculatorForm
    extends Mage_Core_Block_Template
{

    const CALCULATOR_JS = 'mercadopago/mercadopago_calculator.js';

    /**
     * @var $helperData MercadoPago_Core_Helper_Data
     */
    protected $_helperData;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mercadopago/calculator/calculatorForm.phtml');
        $this->_helperData = Mage::helper('mercadopago/data');
    }

    protected function getCalculatorJs()
    {
        return (Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . self::CALCULATOR_JS);
    }

    protected function getTinyUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tiny.min.js';
    }

    protected function getTinyJUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tinyJ.js';
    }

    protected function getPublicKey()
    {
        return Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);
    }

    /**
     * return the Payment methods token configured
     *
     * @return string
     */
    protected function getPaymentMethods()
    {
        return $this->_helperData->getMercadoPagoPaymentMethods(Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN));
    }

    /**
    * Check if current requested URL is secure
    *
    * @return boolean
    */
    public function isCurrentlySecure()
    {
        return Mage::app()->getStore()->isCurrentlySecure();
    }

    /**
     * return the current value of amount
     *
     * @return mixed|bool
     */
    protected function getAmount()
    {
        return $this->getRequest()->getParam('currentAmount');
    }

}