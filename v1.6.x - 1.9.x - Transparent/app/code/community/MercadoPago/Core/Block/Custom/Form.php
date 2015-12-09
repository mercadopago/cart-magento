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
class MercadoPago_Core_Block_Custom_Form
    extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mercadopago/custom/form.phtml');
    }

    protected function _prepareLayout()
    {

        $public_key = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);

        //init js no header
        $block = Mage::app()->getLayout()->createBlock('core/text', 'js_mercadopago');
        $block->setText(
            sprintf(
                '
                <script type="text/javascript">var PublicKeyMercadoPagoCustom = "' . $public_key . '";</script>
                <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
                <script src="http://ui.mlstatic.com/chico/tiny/0.1.1/tiny.min.js"></script>
                <script type="text/javascript" src="%s"></script>
                <script type="text/javascript" src="%s"></script>',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/mercadopago.js',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tinyJ.js'
            )
        );

        $head = Mage::app()->getLayout()->getBlock('after_body_start');

        if ($head) {
            $head->append($block);
        }

        return parent::_prepareLayout();
    }

    /*
     *
     * Only used in Mexico
     *
     */
    public function getCardsPaymentMethods()
    {
        $payment_methods = Mage::getModel('mercadopago/core')->getPaymentMethods();
        $payment_methods_types = array("credit_card", "debit_card", "prepaid_card");
        $types = array();

        //percorre todos os payments methods
        foreach ($payment_methods['response'] as $pm) {

            //filtra por payment_methods
            if (in_array($pm['payment_type_id'], $payment_methods_types)) {
                $types[] = $pm;
            }
        }

        return $types;
    }

    public function getCustomerAndCards()
    {
        $customer = Mage::getModel('mercadopago/custom_payment')->getCustomerAndCards();

        return $customer;
    }
}
