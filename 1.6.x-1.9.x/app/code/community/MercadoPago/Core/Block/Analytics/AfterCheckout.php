<?php

class MercadoPago_Core_Block_Analytics_AfterCheckout
    extends Mage_Core_Block_Template
{

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
        return Mage::registry('mp_analytics_data');
    }
}