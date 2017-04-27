<?php

class MercadoPago_Core_Block_Analytics_Checkout
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
    }

    protected function getAnalyticsData()
    {
        return Mage::helper('mercadopago')->getAnalyticsData();
    }

}