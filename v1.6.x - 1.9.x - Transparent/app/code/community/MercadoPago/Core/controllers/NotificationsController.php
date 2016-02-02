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
class MercadoPago_Core_NotificationsController
    extends Mage_Core_Controller_Front_Action
{
    protected $_return = null;
    protected $_order = null;
    protected $_order_id = null;
    protected $_mpcartid = null;
    protected $_sendemail = false;
    protected $_hash = null;

    protected function _getDataPayments($merchantOrder)
    {
        $data = array();
        $core = Mage::getModel('mercadopago/core');
        foreach ($merchantOrder['payments'] as $payment) {
            $response = $core->getPayment($payment['id']);
            $payment = $response['response']['collection'];
            $data = $this->formatArrayPayment($data, $payment);
        }

        return $data;
    }

    protected function getStatusFinal($dataStatus)
    {
        $status_final = "";
        $statuses = explode('|', $dataStatus);
        foreach ($statuses as $status) {
            if ($status_final == "") {
                $status_final = $status;
            } else {
                if ($status_final != $status) {
                    $status_final = false;
                }
            }
        }

        return $status_final;
    }


    public function standardAction()
    {
        $request = $this->getRequest();
        //notification received
        Mage::helper('mercadopago')->log("Standard Received notification", 'mercadopago-notification.log', $request->getParams());

        $core = Mage::getModel('mercadopago/core');

        $id = $request->getParam('id');
        $topic = $request->getParam('topic');

        if (!empty($id) && $topic == 'merchant_order') {
            $response = $core->getMerchantOrder($id);
            Mage::helper('mercadopago')->log("Return merchant_order", 'mercadopago-notification.log', $response);
            if ($response['status'] == 200 || $response['status'] == 201) {
                $data = array();
                $merchant_order = $response['response'];

                if (count($merchant_order['payments']) > 0) {
                    $data = $this->_getDataPayments($merchant_order);
                    $status_final = $this->getStatusFinal($data['status']);

                    $core->updateOrder($data);

                    if ($status_final != false) {
                        $data['status_final'] = $status_final;
                        Mage::helper('mercadopago')->log("Received Payment data", 'mercadopago-notification.log', $data);
                        $setStatusResponse = $core->setStatusOrder($data);
                        $this->getResponse()->setBody($setStatusResponse['text']);
                        $this->getResponse()->setHttpResponseCode($setStatusResponse['code']);
                    }

                    Mage::dispatchEvent('mercadopago_standard_notification_received',
                        array('payment'        => $data,
                              'merchant_order' => $merchant_order)
                    );

                    return;
                }
            }
        }

        Mage::helper('mercadopago')->log("Merchant Order not found", 'mercadopago-notification.log', $request->getParams());
        $this->getResponse()->setBody("Merchant Order not found");
        $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);
    }

    public function customAction()
    {
        $request = $this->getRequest();
        Mage::helper('mercadopago')->log("Custom Received notification", 'mercadopago-notification.log', $request->getParams());

        $core = Mage::getModel('mercadopago/core');

        $dataId = $request->getParam('data_id');
        $type = $request->getParam('type');
        if (!empty($dataId) && $type == 'payment') {
            $response = $core->getPaymentV1($dataId);
            Mage::helper('mercadopago')->log("Return payment", 'mercadopago-notification.log', $response);

            if ($response['status'] == 200 || $response['status'] == 201) {
                $payment = $response['response'];

                $payment = Mage::helper('mercadopago')->setPayerInfo($payment);

                $core->updateOrder($payment);
                $setStatusResponse = $core->setStatusOrder($payment);
                $this->getResponse()->setBody($setStatusResponse['text']);
                $this->getResponse()->setHttpResponseCode($setStatusResponse['code']);

                return;
            }
        }

        Mage::helper('mercadopago')->log("Payment not found", 'mercadopago-notification.log', $request->getParams());
        $this->getResponse()->getBody("Payment not found");
        $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);
    }

    public function formatArrayPayment($data, $payment)
    {
        Mage::helper('mercadopago')->log("Format Array", 'mercadopago-notification.log');

        $fields = array(
            "status",
            "status_detail",
            "id",
            "payment_method_id",
            "transaction_amount",
            "total_paid_amount",
            "coupon_amount",
            "installments",
            "shipping_cost",
        );

        foreach ($fields as $field) {
            if (isset($payment[$field])) {
                if (isset($data[$field])) {
                    $data[$field] .= " | " . $payment[$field];
                } else {
                    $data[$field] = $payment[$field];
                }
            }
        }

        if (isset($payment["last_four_digits"])) {
            if (isset($data["trunc_card"])) {
                $data["trunc_card"] .= " | " . "xxxx xxxx xxxx " . $payment["last_four_digits"];
            } else {
                $data["trunc_card"] = "xxxx xxxx xxxx " . $payment["last_four_digits"];
            }
        }

        if (isset($payment['cardholder']['name'])) {
            if (isset($data["cardholder_name"])) {
                $data["cardholder_name"] .= " | " . $payment["cardholder"]["name"];
            } else {
                $data["cardholder_name"] = $payment["cardholder"]["name"];
            }
        }

        if (isset($payment['statement_descriptor'])) {
            $data['statement_descriptor'] = $payment['statement_descriptor'];
        }

        $data['external_reference'] = $payment['external_reference'];
        $data['payer_first_name'] = $payment['payer']['first_name'];
        $data['payer_last_name'] = $payment['payer']['last_name'];
        $data['payer_email'] = $payment['payer']['email'];

        return $data;
    }

}
