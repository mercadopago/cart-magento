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

        return  ($this->_helperData->isValidAccessToken(Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN))
        & $this->_helperData->isAvailableCalculator());

    }

    /**
     * @param $nameLayoutConteiner string
     * @return bool
     */
    protected function isPageToShow($nameLayoutConteiner){

        $valueConfig = $this->_helperData->getPagesToShow();
        $pages = explode(',', $valueConfig);

        return in_array($nameLayoutConteiner, $pages);
    }

    /**
     * @param $nameLayoutConteiner string
     * @return bool
     */
    protected function inPagePDP($nameLayoutConteiner){

        return $nameLayoutConteiner === self::PAGE_PDP;
    }

    /**
     * @param $nameLayoutConteiner string
     * @return bool
     */
    protected function inPageCheckoutCart($nameLayoutConteiner){

        return $nameLayoutConteiner === self::PAGE_CART;
    }

}