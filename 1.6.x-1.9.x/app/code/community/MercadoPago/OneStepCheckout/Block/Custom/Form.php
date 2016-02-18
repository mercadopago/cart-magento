<?php

class MercadoPago_OneStepCheckout_Block_Custom_Form
    extends MercadoPago_Core_Block_Custom_Form
{

    protected function _construct()
    {
        if (Mage::helper('mercadopago_onestepcheckout')->isOneStepCheckoutActive()) {
            Mage_Payment_Block_Form_Cc::_construct();
            $this->setTemplate('mercadopago/onestepcheckout/custom/form.phtml');
        } else {
            parent::_construct();
        }
    }

    protected function _prepareLayout()
    {

        $public_key = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);

        //init js no header
        $block = Mage::app()->getLayout()->createBlock('core/text', 'js_mercadopago');
        if (Mage::helper('mercadopago_onestepcheckout')->isOneStepCheckoutActive()) {
            $block->setText(
                sprintf(
                    '
                    <script type="text/javascript">var PublicKeyMercadoPagoCustom = "' . $public_key . '";</script>
                    <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
                    <script src="http://ui.mlstatic.com/chico/tiny/0.1.1/tiny.min.js"></script>
                    <script type="text/javascript" src="%s"></script>
                    <script type="text/javascript" src="%s"></script>
                    <script type="text/javascript" src="%s"></script>',
                    Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/mercadopago_osc.js',
                    Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tiny.min.js',
                    Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tinyJ.js'
                )
            );
            $head = Mage::app()->getLayout()->getBlock('after_body_start');

            if ($head) {
                $head->append($block);
            }

            return Mage_Payment_Block_Form_Cc::_prepareLayout();
        } else {
            return parent::_prepareLayout();
        }

    }
}
