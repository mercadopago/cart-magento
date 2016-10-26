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

    protected $_requestData = [];
    protected $_merchantOrder = [];
    protected $_paymentData = [];
    protected $_core;
    protected $_helper;
    protected $_statusHelper;
    protected $_order;
    protected $_shipmentData;
    protected $_statusFinal;

    const LOG_FILE = 'mercadopago-notification.log';


    public function standardAction()
    {
        $this->_requestData = $this->getRequest()->getParams();
        //notification received
        $this->_helper = Mage::helper('mercadopago');
        $this->_core = Mage::getModel('mercadopago/core');
        $this->_statusHelper = Mage::helper('mercadopago/statusUpdate');
        $this->_shipmentData = '';

        $this->_helper->log('Standard Received notification', self::LOG_FILE, $this->_requestData);
        if ($this->_emptyParams($this->_getRequestData('id'), $this->_getRequestData('topic'))) {

            return;
        }
        switch ($this->_getRequestData('topic')) {
            case 'merchant_order':
                if (!$this->_handleMerchantOrder()) {
                    return;
                }
                break;
            case 'payment':
                $this->_paymentData = $this->_getFormattedPaymentData($this->_getRequestData('id'));
                $this->_statusFinal = $this->_paymentData['status'];
                break;
            default:
                $this->_responseLog();

                return;
        }
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->_paymentData["external_reference"]);
        if (!$this->_orderExists()) {
            return;
        }

        $this->_helper->log('Update Order', self::LOG_FILE);
        $this->_statusHelper->setStatusUpdated($this->_paymentData, $this->_order);
        $this->_core->updateOrder($this->_order, $this->_paymentData);
        $this->_dispatchBeforeSetEvent();

        if ($this->_statusFinal != false) {
            $this->_paymentData['status_final'] = $this->_statusFinal;
            $this->_helper->log('Received Payment data', self::LOG_FILE, $this->_paymentData);
            $setStatusResponse = $this->_statusHelper->setStatusOrder($this->_paymentData);
            $this->_setResponse($setStatusResponse['body'], $setStatusResponse['code']);
        } else {
            $this->_setResponse(MercadoPago_Core_Helper_Response::INFO_STATUS_NOT_FINAL, MercadoPago_Core_Helper_Response::HTTP_OK);
        }

        $this->_dispatchNotificationEvent();
        $this->_responseLog();
    }

    public function customAction()
    {
        $request = $this->getRequest();
        $this->_helper = Mage::helper('mercadopago');
        $this->_core = Mage::getModel('mercadopago/core');
        $this->_statusHelper = Mage::helper('mercadopago/statusUpdate');
        $this->_helper->log('Custom Received notification', self::LOG_FILE, $request->getParams());
        $dataId = $request->getParam('data_id');
        $type = $request->getParam('type');
        if (!empty($dataId) && $type == 'payment') {
            $response = $this->_core->getPaymentV1($dataId);
            $this->_helper->log('Return payment', self::LOG_FILE, $response);

            if ($this->_isValidResponse($response)) {
                $payment = $response['response'];

                $payment = $this->_helper->setPayerInfo($payment);
                $this->_order = Mage::getModel('sales/order')->loadByIncrementId($payment['external_reference']);
                if (!$this->_orderExists()) {
                    return;
                }
                $this->_helper->log('Update Order', self::LOG_FILE);
                $this->_statusHelper->setStatusUpdated($payment, $this->_order);
                $this->_core->updateOrder($this->_order, $payment);
                $setStatusResponse = $this->_statusHelper->setStatusOrder($payment);
                $this->_setResponse($setStatusResponse['body'], $setStatusResponse['code']);
                $this->_helper->log('Http code', self::LOG_FILE, $this->getResponse()->getHttpResponseCode());

                return;
            }
        }

        $this->_helper->log('Payment not found', self::LOG_FILE, $request->getParams());
        $this->_setResponse('Payment not found', MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);
        $this->_helper->log('Http code', self::LOG_FILE, $this->getResponse()->getHttpResponseCode());
    }

    protected function _handleMerchantOrder()
    {
        $merchantOrder = $this->_core->getMerchantOrder($this->_getRequestData('id'));
        $this->_helper->log('Return merchant_order', self::LOG_FILE, $merchantOrder);
        if (!$this->_isValidMerchantOrder($merchantOrder)) {
            $this->_helper->log(MercadoPago_Core_Helper_Response::INFO_MERCHANT_ORDER_NOT_FOUND, self::LOG_FILE, $this->_requestData);
            $this->_setResponse(MercadoPago_Core_Helper_Response::INFO_MERCHANT_ORDER_NOT_FOUND, MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);

            return false;
        }

        $this->_paymentData = $this->_getDataPayments();
        $this->_statusFinal = $this->_statusHelper->getStatusFinal($this->_paymentData['status'], $this->_merchantOrder);
        $this->_shipmentData = $this->_getShipmentsArray();

        return true;
    }

    protected function _getDataPayments()
    {
        $data = array();
        foreach ($this->_merchantOrder['payments'] as $payment) {
            $data = $this->_getFormattedPaymentData($payment['id'], $data);
        }

        return $data;
    }

    protected function _getFormattedPaymentData($paymentId, $data = [])
    {
        $response = $this->_core->getPayment($paymentId);
        $payment = $response['response']['collection'];

        return $this->formatArrayPayment($data, $payment);
    }

    protected function _responseLog()
    {
        $this->_helper->log("Http code", self::LOG_FILE, $this->getResponse()->getHttpResponseCode());
    }

    protected function _shipmentExists($shipmentData)
    {
        return (!empty($shipmentData) && !empty($this->merchantOrder));
    }

    protected function _getShipmentsArray()
    {
        return (isset($this->_merchantOrder['shipments'][0])) ? $this->_merchantOrder['shipments'][0] : [];
    }

    protected function _isValidMerchantOrder($merchantOrder)
    {
        $this->_merchantOrder = $merchantOrder['response'];
        if ($this->_isValidResponse($merchantOrder) && count($this->_merchantOrder['payments']) > 0) {
            $this->_responseLog();

            return true;
        }

        return false;
    }

    protected function _isValidResponse($response) {
        return ($response['status'] == 200 || $response['status'] == 201);
    }

    protected function _emptyParams($p1, $p2)
    {
        return (empty($p1) || empty($p2));
    }

    protected function _orderExists()
    {
        if ($this->_order->getId()) {
            return true;
        }
        $this->_helper->log(MercadoPago_Core_Helper_Response::INFO_EXTERNAL_REFERENCE_NOT_FOUND, self::LOG_FILE, $this->_requestData);
        $this->_setResponse(MercadoPago_Core_Helper_Response::INFO_EXTERNAL_REFERENCE_NOT_FOUND, MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);

        return false;
    }

    protected function _setResponse($body, $code)
    {
        $this->getResponse()->setBody($body);
        $this->getResponse()->setHttpResponseCode($code);
    }

    protected function _dispatchBeforeSetEvent()
    {
        if ($this->_shipmentExists($this->_shipmentData)) {
            Mage::dispatchEvent('mercadopago_standard_notification_before_set_status',
                ['shipmentData' => $this->_shipmentData,
                 'orderId'      => $this->_merchantOrder['external_reference']]
            );
        }
    }

    protected function _dispatchNotificationEvent()
    {
        if ($this->_shipmentExists($this->_shipmentData)) {
            Mage::dispatchEvent('mercadopago_standard_notification_received',
                ['payment'        => $this->_paymentData,
                 'merchant_order' => $this->_merchantOrder]
            );
        }
    }

    protected function _getRequestData($key = null)
    {
        if (null === $key) {
            return $this->_requestData;
        }

        return isset($this->_requestData[$key]) ? $this->_requestData[$key] : null;
    }


    public function formatArrayPayment($data, $payment)
    {
        Mage::helper('mercadopago')->log("Format Array", self::LOG_FILE);

        $fields = [
            "status",
            "status_detail",
            "payment_id_detail",
            "id",
            "payment_method_id",
            "transaction_amount",
            "total_paid_amount",
            "coupon_amount",
            "installments",
            "shipping_cost",
            "amount_refunded",
        ];

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
