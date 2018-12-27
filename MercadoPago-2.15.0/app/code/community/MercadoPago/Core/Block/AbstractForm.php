<?php

class MercadoPago_Core_Block_AbstractForm
    extends Mage_Payment_Block_Form_Cc
{
    protected function _prepareLayout()
    {
        //init js no header
        $block = Mage::app()->getLayout()->createBlock('core/text', 'js_mercadopago');
        $block->setText(
            sprintf(
                '
                  <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
                  <script type="text/javascript" src="%s"></script>
                  <script type="text/javascript" src="%s"></script>
                  <link rel="stylesheet" href="%s"/>
                  <link rel="stylesheet" href="%s"/>
                ',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/MPv1.js',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/MPv1Ticket.js',
                $this->getSkinUrl('mercadopago/css/custom_checkout_mercadopago.css') . "?nocache=" . rand(),
                $this->getSkinUrl('mercadopago/css/MPv1.css') . "?nocache=" . rand()
            )
        );

        $head = Mage::app()->getLayout()->getBlock('after_body_start');

        if ($head) {
            $head->append($block);
        }

        return parent::_prepareLayout();
    }
}
