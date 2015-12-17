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


class MercadoPago_Core_Block_Customticket_Form
    extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('mercadopago/custom_ticket/form.phtml');
    }

    protected function _prepareLayout()
    {

        //pega public key para settar no aquivo mercadopago.js
        $public_key = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);

        //init js no header
        $block = Mage::app()->getLayout()->createBlock('core/text', 'js_mercadopago');
        $block->setText(
            sprintf(
                '
                <script type="text/javascript">var PublicKeyMercadoPagoCustom = "' . $public_key .'";</script>
                <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
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

    public function getTicketsOptions()
    {
        $payment_methods = Mage::getModel('mercadopago/core')->getPaymentMethods();
        $tickets = array();

        //percorre todos os payments methods
        foreach ($payment_methods['response'] as $pm) {

            //filtra por tickets
            if ($pm['payment_type_id'] == "ticket") {
                $tickets[] = $pm;
            }
        }

        return $tickets;
    }
}
