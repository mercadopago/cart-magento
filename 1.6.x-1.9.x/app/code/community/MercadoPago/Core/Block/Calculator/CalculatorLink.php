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


class MercadoPago_Core_Block_Calculator_CalculatorLink
    extends Mage_Core_Block_Template
{

    /**
     * @var $helperData MercadoPago_Core_Helper_Data
     */
    protected $helperData;

    protected function _construct()
    {
        parent::_construct();
//        $this->setTemplate('mercadopago/calculator/calculatorLink.phtml');
        $this->helperData = Mage::helper('mercadopago/data');
    }


    /**
     *
     *
     * @return bool
     */
    protected function isAvailableCalculator(){
        return $this->helperData->isAvailableCalculator();
    }

    /**
     * @param $nameLayoutConteiner string
     * @return bool
     */
    protected function isPageToShow($nameLayoutConteiner){

        $valueConfig = $this->helperData->getPagesToShow();
        $pages = explode(',', $valueConfig);

        return in_array($nameLayoutConteiner, $pages);
    }
}