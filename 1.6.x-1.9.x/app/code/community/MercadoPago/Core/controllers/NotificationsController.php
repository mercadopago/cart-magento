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
        $data = array();
        $core = Mage::getModel('mercadopago/core');
        foreach ($merchantOrder['payments'] as $payment) {
            $response = $core->getPayment($payment['id']);
            $payment = $response['response']['collection'];
            $data = $this->formatArrayPayment($data, $payment);
        }

        return $data;
    }

    protected function _dateCompare($a, $b)
    {
        $t1 = strtotime($a['value']);
        $t2 = strtotime($b['value']);

        return $t2 - $t1;
    }

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

    protected function getStatusFinal($dataStatus, $payments)
    {
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


    public function standardAction()
    {
        $request = $this->getRequest();
        //notification received
        Mage::helper('mercadopago')->log("Standard Received notification", self::LOG_FILE, $request->getParams());

        $core = Mage::getModel('mercadopago/core');

        $id = $request->getParam('id');
        $topic = $request->getParam('topic');

        if (!empty($id) && $topic == 'merchant_order') {
            $response = $core->getMerchantOrder($id);
            Mage::helper('mercadopago')->log("Return merchant_order", self::LOG_FILE, $response);
            if ($response['status'] == 200 || $response['status'] == 201) {
                $merchant_order = $response['response'];

                if (count($merchant_order['payments']) > 0) {
                    $data = $this->_getDataPayments($merchant_order);
                    if ($merchant_order['total_amount'] == $merchant_order['paid_amount']) {
                        $status_final = 'approved';
                    } else {
                        $status_final = $this->getStatusFinal($data['status'], $merchant_order['payments']);
                    }
                    $shipmentData = (isset($merchant_order['shipments'][0])) ? $merchant_order['shipments'][0] : [];
                    Mage::helper('mercadopago')->log("Update Order", self::LOG_FILE);
                    Mage::helper('mercadopago')->setStatusUpdated($data);
                    $core->updateOrder($data);
                    if (!empty($shipmentData)) {
                        Mage::dispatchEvent('mercadopago_standard_notification_before_set_status',
                            array('shipmentData' => $shipmentData,
                                  'orderId'      => $merchant_order['external_reference'])
                        );
                    }
                    if ($status_final != false) {
                        $data['status_final'] = $status_final;
                        Mage::helper('mercadopago')->log("Received Payment data", self::LOG_FILE, $data);
                        $setStatusResponse = $core->setStatusOrder($data);
                        $this->getResponse()->setBody($setStatusResponse['text']);
                        $this->getResponse()->setHttpResponseCode($setStatusResponse['code']);
                    } else {
                        $this->getResponse()->setBody("Status not final");
                        $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_OK);
                    }

                    Mage::dispatchEvent('mercadopago_standard_notification_received',
                        array('payment'        => $data,
                              'merchant_order' => $merchant_order)
                    );

                    Mage::helper('mercadopago')->log("Http code", self::LOG_FILE, $this->getResponse()->getHttpResponseCode());

                    return;
                }
            }
        } else {
            Mage::helper('mercadopago')->log("Merchant Order not found", self::LOG_FILE, $request->getParams());
            $this->getResponse()->setBody("Merchant Order not found");
            $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);
        }

        Mage::helper('mercadopago')->log("Http code", self::LOG_FILE, $this->getResponse()->getHttpResponseCode());
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
