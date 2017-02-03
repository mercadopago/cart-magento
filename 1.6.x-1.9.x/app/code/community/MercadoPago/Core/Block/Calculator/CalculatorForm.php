<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category       Payment Gateway
 * @package        MercadoPago
 * @author         Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 * @copyright      Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


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

    protected function getCalculatorJs(){
        return (Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . self::CALCULATOR_JS);
    }

    protected function getTinyUrl(){
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tiny.min.js';
    }
    protected function getTinyJUrl(){
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tinyJ.js';
    }

    protected function getPublicKey(){
        return Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);
    }


    /**
     * Check if the access token is valid, if the API is not down and if the configuration is enabled
     *
     * @return bool
     */
    protected function getPaymentMethods(){

        return  $this->_helperData->getPaymentMethods(Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN));

    }
}