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
    protected $_finalStatus = ['rejected', 'cancelled', 'refunded', 'charge_back'];
    protected $_notFinalStatus = ['authorized', 'process', 'in_mediation'];

    const LOG_FILE = 'mercadopago-notification.log';

    protected function _getDataPayments($merchantOrder)
    {
        $core = Mage::getModel('mercadopago/core');
        $data = array();
        foreach ($merchantOrder['payments'] as $payment) {
            $data = $this->_getFormattedPaymentData($payment['id'], $core, $data);
        }

        return $data;
    }

    protected function _getFormattedPaymentData($paymentId, $core, $data = [])
    {
        $response = $core->getPayment($paymentId);
        $payment = $response['response']['collection'];

        return $this->formatArrayPayment($data, $payment);
    }

    protected function _dateCompare($a, $b)
    {
        $t1 = strtotime($a['value']);
        $t2 = strtotime($b['value']);

        return $t2 - $t1;
    }

    /**
     * @param $payments
     * @param $status
     *
     * @return int
     */
    protected function _getLastPaymentIndex($payments, $status)
    {
        $dates = [];
        foreach ($payments as $key => $payment) {
            if (in_array($payment['status'], $status)) {
                $dates[] = ['key' => $key, 'value' => $payment['last_modified']];
            }
        }
        usort($dates, array(get_class($this), "_dateCompare"));
        if ($dates) {
            $lastModified = array_pop($dates);

            return $lastModified['key'];
        }

        return 0;
    }

    /**
     * Returns status that must be set to order, if a not final status exists
     * then the last of this statuses is returned. Else the last of final statuses
     * is returned
     *
     * @param $dataStatus
     * @param $merchantOrder
     *
     * @return string
     */
    protected function getStatusFinal($dataStatus, $merchantOrder)
    {
        if (isset($merchantOrder['paid_amount']) && $merchantOrder['total_amount'] == $merchantOrder['paid_amount']) {
            return 'approved';
        }
        $payments = $merchantOrder['payments'];
        $statuses = explode('|', $dataStatus);
        foreach ($statuses as $status) {
            $status = str_replace(' ', '', $status);
            if (in_array($status, $this->_notFinalStatus)) {
                $lastPaymentIndex = $this->_getLastPaymentIndex($payments, $this->_notFinalStatus);

                return $payments[$lastPaymentIndex]['status'];
            }
        }

        $lastPaymentIndex = $this->_getLastPaymentIndex($payments, $this->_finalStatus);

        return $payments[$lastPaymentIndex]['status'];
    }

    protected function _responseLog($helper)
    {
        $helper->log("Http code", self::LOG_FILE, $this->getResponse()->getHttpResponseCode());
    }

    protected function _shipmentExists($shipmentData, $merchantOrder)
    {
        return (!empty($shipmentData) && !empty($merchantOrder));
    }

    protected function _getShipmentsArray($merchantOrder)
    {
        return (isset($merchantOrder['shipments'][0])) ? $merchantOrder['shipments'][0] : [];
    }

    protected function _isValidResponse($response)
    {
        return ($response['status'] == 200 || $response['status'] == 201);
    }

    protected function _emptyParams($p1, $p2)
    {
        return (empty($p1) || empty($p2));
    }

    public function standardAction()
    {
        $request = $this->getRequest();
        //notification received
        $helper = Mage::helper('mercadopago');
        $shipmentData = '';
        $merchantOrder = '';
        $helper->log("Standard Received notification", self::LOG_FILE, $request->getParams());
        $core = Mage::getModel('mercadopago/core');

        $id = $request->getParam('id');
        $topic = $request->getParam('topic');
        if ($this->_emptyParams($id, $topic)) {
            $helper->log("Merchant Order not found", self::LOG_FILE, $request->getParams());
            $this->getResponse()->setBody("Merchant Order not found");
            $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);

            return;
        }

        if ($topic == 'merchant_order') {
            $response = $core->getMerchantOrder($id);
            $helper->log("Return merchant_order", self::LOG_FILE, $response);
            if (!$this->_isValidResponse($response)) {
                $this->_responseLog($helper);

                return;
            }
            $merchantOrder = $response['response'];
            if (count($merchantOrder['payments']) == 0) {
                $this->_responseLog($helper);

                return;
            }
            $data = $this->_getDataPayments($merchantOrder);
            $statusFinal = $this->getStatusFinal($data['status'], $merchantOrder);
            $shipmentData = $this->_getShipmentsArray($merchantOrder);
        } elseif ($topic == 'payment') {
            $data = $this->_getFormattedPaymentData($id, $core);
            $statusFinal = $data['status'];
        } else {
            $this->_responseLog($helper);

            return;
        }

        $helper->log("Update Order", self::LOG_FILE);
        $helper->setStatusUpdated($data);
        $core->updateOrder($data);
        if ($this->_shipmentExists($shipmentData, $merchantOrder)) {
            Mage::dispatchEvent('mercadopago_standard_notification_before_set_status',
                array('shipmentData' => $shipmentData,
                      'orderId'      => $merchantOrder['external_reference'])
            );
        }
        if ($statusFinal != false) {
            $data['status_final'] = $statusFinal;
            $helper->log("Received Payment data", self::LOG_FILE, $data);
            $setStatusResponse = $core->setStatusOrder($data);
            $this->getResponse()->setBody($setStatusResponse['text']);
            $this->getResponse()->setHttpResponseCode($setStatusResponse['code']);
        } else {
            $this->getResponse()->setBody("Status not final");
            $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_OK);
        }

        if ($this->_shipmentExists($shipmentData, $merchantOrder)) {
            Mage::dispatchEvent('mercadopago_standard_notification_received',
                array('payment'        => $data,
                      'merchant_order' => $merchantOrder)
            );
        }


        $this->_responseLog($helper);
    }

    public function customAction()
    {
        $request = $this->getRequest();
        Mage::helper('mercadopago')->log("Custom Received notification", self::LOG_FILE, $request->getParams());

        $core = Mage::getModel('mercadopago/core');

        $dataId = $request->getParam('data_id');
        $type = $request->getParam('type');
        if (!empty($dataId) && $type == 'payment') {
            $response = $core->getPaymentV1($dataId);
            Mage::helper('mercadopago')->log("Return payment", self::LOG_FILE, $response);

            if ($response['status'] == 200 || $response['status'] == 201) {
                $payment = $response['response'];

                $payment = Mage::helper('mercadopago')->setPayerInfo($payment);

                Mage::helper('mercadopago')->log("Update Order", self::LOG_FILE);
                Mage::helper('mercadopago')->setStatusUpdated($payment);
                $core->updateOrder($payment);
                $setStatusResponse = $core->setStatusOrder($payment);
                $this->getResponse()->setBody($setStatusResponse['text']);
                $this->getResponse()->setHttpResponseCode($setStatusResponse['code']);
                Mage::helper('mercadopago')->log("Http code", self::LOG_FILE, $this->getResponse()->getHttpResponseCode());

                return;
            }
        }

        Mage::helper('mercadopago')->log("Payment not found", self::LOG_FILE, $request->getParams());
        $this->getResponse()->getBody("Payment not found");
        $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);
        Mage::helper('mercadopago')->log("Http code", self::LOG_FILE, $this->getResponse()->getHttpResponseCode());
    }

    public function formatArrayPayment($data, $payment)
    {
        Mage::helper('mercadopago')->log("Format Array", self::LOG_FILE);

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
            "amount_refunded",
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
