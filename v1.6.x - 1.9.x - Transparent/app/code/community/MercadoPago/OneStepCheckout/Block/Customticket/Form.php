<?php

class MercadoPago_OneStepCheckout_Block_Customticket_Form
    extends MercadoPago_Core_Block_Customticket_Form
{
    protected function _prepareLayout()
    {

        //pega public key para settar no aquivo mercadopago.js
        $public_key = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);

        //init js no header
        $block = Mage::app()->getLayout()->createBlock('core/text', 'js_mercadopago');
        if (Mage::getStoreConfigFlag(MercadoPago_OneStepCheckout_Helper_Data::XML_PATH_ONS_ACTIVE)) {
            $block->setText(
                sprintf(
                    '
                    <script type="text/javascript">var PublicKeyMercadoPagoCustom = "' . $public_key . '";</script>
                    <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
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
