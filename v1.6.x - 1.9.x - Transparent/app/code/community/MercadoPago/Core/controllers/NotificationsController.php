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

                    $this->updateOrder($data);

                    if ($status_final != false) {
                        $data['status_final'] = $status_final;
                        $this->setStatusOrder($data);
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

                $payment["trunc_card"] = "xxxx xxxx xxxx " . $payment['card']["last_four_digits"];
                $payment["cardholder_name"] = $payment['card']["cardholder"]["name"];
                $payment['payer_first_name'] = $payment['payer']['first_name'];
                $payment['payer_last_name'] = $payment['payer']['last_name'];
                $payment['payer_email'] = $payment['payer']['email'];

                $this->updateOrder($payment);
                $this->setStatusOrder($payment);

                return;
            }
        }

        Mage::helper('mercadopago')->log("Payment not found", 'mercadopago-notification.log', $request->getParams());
        $this->getResponse()->getBody("Payment not found");
        $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);
    }

    public function updateOrder($data)
    {
        Mage::helper('mercadopago')->log("Update Order", 'mercadopago-notification.log');

        try {
            $order = Mage::getModel('sales/order')->loadByIncrementId($data["external_reference"]);

            //update info de status no pagamento
            $payment_order = $order->getPayment();

            $additionalFields = array(
                'status',
                'status_detail',
                'payment_id',
                'transaction_amount',
                'cardholderName',
                'installments',
                'statement_descriptor',
                'trunc_card'

            );

            foreach ($additionalFields as $field) {
                if (isset($data[$field])) {
                    $payment_order->setAdditionalInformation($field, $data[$field]);
                }
            }

            if (isset($data['payment_method_id'])) {
                $payment_order->setAdditionalInformation('payment_method', $data['payment_method_id']);
            }

            $payment_status = $payment_order->save();
            Mage::helper('mercadopago')->log("Update Payment", 'mercadopago-notification.log', $payment_status->toString());

            if ($data['payer_first_name']) {
                $order->setCustomerFirstname($data['payer_first_name']);
            }

            if ($data['payer_last_name']) {
                $order->setCustomerLastname($data['payer_last_name']);
            }

            if ($data['payer_email']) {
                $order->setCustomerEmail($data['payer_email']);
            }

            if ($data['coupon_amount']) {
                $order->setDiscountCouponAmount($data['coupon_amount'] * -1);
                $order->setBaseDiscountCouponAmount($data['coupon_amount'] * -1);
                $balance = $data['total_paid_amount'] - ($data['transaction_amount'] - $data['coupon_amount'] + $data['shipping_cost']);
            } else {
                $balance = $data['total_paid_amount'] - $data['transaction_amount'] - $data['shipping_cost'];
            }

            if ($balance > 0) {
                $order->setFinanceCostAmount($balance);
                $order->setBaseFinanceCostAmount($balance);
            }

            $order->setGrandTotal($data['total_paid_amount']);

            $status_save = $order->save();
            Mage::helper('mercadopago')->log("Update order", 'mercadopago-notification.log', $status_save->toString());
        } catch (Exception $e) {
            Mage::helper('mercadopago')->log("erro in update order status: " . $e, 'mercadopago-notification.log');
            $this->getResponse()->setBody($e);

            //caso erro no processo de notificação de pagamento, mercadopago ira notificar novamente.
            $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_BAD_REQUEST);
        }
    }

    protected function getStatusOrder($status)
    {
        switch ($status) {
            case 'approved': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_approved');
                break;
            }
            case 'refunded': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_refunded');
                break;
            }
            case 'in_mediation': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_mediation');
                break;
            }
            case 'cancelled': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_cancelled');
                break;
            }
            case 'rejected': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_rejected');
                break;
            }
            case 'chargeback': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_chargeback');
                break;
            }
            default: {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
            }
        }

        return $status;
    }

    protected function getMessage($status, $payment)
    {
        $rawMessage = Mage::helper('mercadopago')->__(Mage::helper('mercadopago/statusOrderMessage')->getMessage($status));
        $rawMessage .= Mage::helper('mercadopago')->__('<br/> Payment id: %s', $payment['id']);
        $rawMessage .= Mage::helper('mercadopago')->__('<br/> Status: %s', $payment['status']);
        $rawMessage .= Mage::helper('mercadopago')->__('<br/> Status Detail: %s', $payment['status_detail']);

        return $rawMessage;
    }

    public function setStatusOrder($payment)
    {
        Mage::helper('mercadopago')->log("Received Payment data", 'mercadopago-notification.log', $payment);

        $order = Mage::getModel('sales/order')->loadByIncrementId($payment["external_reference"]);
        $status = $payment['status'];

        if (isset($payment['status_final'])) {
            $status = $payment['status_final'];
        }
        $message = $this->getMessage($status, $payment);

        try {
            if ($status == 'approved') {
                $invoice = $order->prepareInvoice();
                $invoice->register()->pay();
                Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();

                $invoice->sendEmail(true, $message);
            } elseif ($status == 'refunded' || $status == 'cancelled') {
                $order->cancel();
            }

            $statusOrder = $this->getStatusOrder($status);

            $order->addStatusToHistory($statusOrder, $message, true);
            $order->sendOrderUpdateEmail(true, $message);

            $status_save = $order->save();
            Mage::helper('mercadopago')->log("Update order", 'mercadopago-notification.log', $status_save->toString());
            Mage::helper('mercadopago')->log($message, 'mercadopago-notification.log');

            $this->getResponse()->setBody($message);
        } catch (Exception $e) {
            Mage::helper('mercadopago')->log("erro in set order status: " . $e, 'mercadopago-notification.log');
            $this->getResponse()->setBody($e);
            $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_BAD_REQUEST);
        }
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
