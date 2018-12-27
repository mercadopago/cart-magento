<?php

class MercadoPago_Core_Block_Calculator_CalculatorLink
    extends Mage_Core_Block_Template
{

    const PAGE_PDP = 'product.info.calculator';
    const PAGE_CART = 'checkout.cart.calculator';

    /**
     * @var $helperData MercadoPago_Core_Helper_Data
     */
    protected $_helperData;

    protected function _construct()
    {
        parent::_construct();
        $this->_helperData = Mage::helper('mercadopago/data');
    }


    /**
     * Check if the access token is valid, if the API is not down and if the configuration is enabled
     *
     * @return bool
     */
    protected function isAvailableCalculator(){

        $isValidAccessToken = $this->_helperData->isValidAccessToken(Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN));
        $pk = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);
        return  ($isValidAccessToken & !empty($pk) & $this->_helperData->isAvailableCalculator());

    }

    /**
     * @param $nameLayoutContainer string
     * @return bool
     */
    protected function isPageToShow($nameLayoutContainer){

        $valueConfig = $this->_helperData->getPagesToShow();
        $pages = explode(',', $valueConfig);

        return in_array($nameLayoutContainer, $pages);
    }

    /**
     * @param $nameLayoutContainer string
     * @return bool
     */
    protected function inPagePDP($nameLayoutContainer){

        return $nameLayoutContainer === self::PAGE_PDP;
    }

    /**
     * @param $nameLayoutContainer string
     * @return bool
     */
    protected function inPageCheckoutCart($nameLayoutContainer){

        return $nameLayoutContainer === self::PAGE_CART;
    }

}