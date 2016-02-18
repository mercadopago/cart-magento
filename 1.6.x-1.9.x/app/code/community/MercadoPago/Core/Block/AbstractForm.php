<?php

class MercadoPago_Core_Block_AbstractForm
    extends Mage_Payment_Block_Form_Cc
{
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
                <script type="text/javascript" src="%s"></script>
                <script type="text/javascript" src="%s"></script>
                <script type="text/javascript" src="%s"></script>',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/mercadopago.js',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tiny.min.js',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/tinyJ.js'
            )
        );

        $head = Mage::app()->getLayout()->getBlock('after_body_start');

        if ($head) {
            $head->append($block);
        }

        return parent::_prepareLayout();
    }
}