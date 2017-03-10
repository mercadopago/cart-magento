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
class MercadoPago_Core_Model_CustomTicket_Payment
    extends MercadoPago_Core_Model_CustomPayment
{
    protected $_formBlockType = 'mercadopago/customticket_form';
    protected $_infoBlockType = 'mercadopago/customticket_info';

    protected $_code = 'mercadopago_customticket';

    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initialize($paymentAction, $stateObject)
    {
        $response = $this->preparePostPayment();

        if ($response !== false) {
            $this->getInfoInstance()->setAdditionalInformation('activation_uri', $response['response']['transaction_details']['external_resource_url']);
            $this->getInfoInstance()->setAdditionalInformation('payment_id_detail', $response['response']['id']);
            return true;
        }

        return false;
    }

    public function assignData($data)
    {
        // route /checkout/onepage/savePayment
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $infoForm = $data->getData();
        $infoForm = $infoForm['mercadopago_customticket'];

        Mage::helper('mercadopago')->log("info form", 'mercadopago-custom.log', $infoForm);

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('payment_method', $infoForm['payment_method_ticket']);



        if (isset($infoForm['coupon_code'])) {
            $info->setAdditionalInformation('coupon_code', $infoForm['coupon_code']);
        }

        return $this;
    }

    public function preparePostPayment()
    {
        Mage::helper('mercadopago')->log("Ticket -> init prepare post payment", 'mercadopago-custom.log');
        $core = Mage::getModel('mercadopago/core');
        $quote = $this->_getQuote();
        $orderId = $quote->getReservedOrderId();
        $order = $this->_getOrder($orderId);

        $payment = $order->getPayment();

        $paymentInfo = [];

        if ($payment->getAdditionalInformation("coupon_code") != "") {
            $paymentInfo['coupon_code'] = $payment->getAdditionalInformation("coupon_code");
        }

        $preference = $core->makeDefaultPreferencePaymentV1($paymentInfo);

        $preference['payment_method_id'] = $payment->getAdditionalInformation("payment_method");

        Mage::helper('mercadopago')->log("Ticket -> PREFERENCE to POST /v1/payments", 'mercadopago-custom.log', $preference);

        /* POST /v1/payments */

        return $core->postPaymentV1($preference);
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('mercadopago/success', ['_secure' => true]);
    }

}
