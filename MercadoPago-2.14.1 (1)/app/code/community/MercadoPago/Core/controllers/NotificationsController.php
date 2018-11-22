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

    protected $_requestData = array();
    protected $_merchantOrder = array();
    protected $_paymentData = array();
    protected $_core;
    protected $_helper;
    protected $_statusHelper;
    protected $_order;
    protected $_shipmentData;
    protected $_statusFinal;

    const LOG_FILE = 'mercadopago-notification.log';

    protected function getCore() {
        if (empty($this->_core)) {
            $this->_core = Mage::getModel('mercadopago/core');
        }
        return $this->_core;
    }

    public function indexAction()
    {
        $params = $this->getRequest()->getParams();
        Mage::helper('mercadopago')->log('Received notification', self::LOG_FILE, $params);
        if (isset($params['topic'])) {
            switch($params['topic']) {
                case MercadoPago_Core_Helper_Response::TOPIC_RECURRING_PAYMENT: {
                    $this->_forward('recurring');
                    break;
                }
                case MercadoPago_Core_Helper_Response::TOPIC_PAYMENT: {
                    $this->_forward('recurringPayment');
                    break;
                }
            }
        }
    }

    public function standardAction()
    {
        $this->_requestData = $this->getRequest()->getParams();
        //notification received
        $this->_helper = Mage::helper('mercadopago');
        $this->_statusHelper = Mage::helper('mercadopago/statusUpdate');
        $this->_shipmentData = '';

        $this->_helper->log('Standard Received notification', self::LOG_FILE, $this->_requestData);
        if ($this->_emptyParams($this->_getRequestData('id'), $this->_getRequestData('topic'))) {

            return;
        }
        switch ($this->_getRequestData('topic')) {
            case 'merchant_order':
                if (!$this->_handleMerchantOrder($this->_getRequestData('id'))) {
                    return;
                }
                break;
            case 'payment':
                $this->_paymentData = $this->_getFormattedPaymentData($this->_getRequestData('id'));

                if (empty($this->_paymentData)) {
                  return;
                }

                if (!$this->_handleMerchantOrder($this->_paymentData['merchant_order_id'])) {
                  return;
                }
               
                break;
            default:
                $this->_responseLog();

                return;
        }

        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->_paymentData["external_reference"]);
        if ($this->_order->getStatus() == 'canceled') {
            $this->_helper->log(MercadoPago_Core_Helper_Response::INFO_ORDER_CANCELED, self::LOG_FILE, $this->_requestData);
            $this->_setResponse(MercadoPago_Core_Helper_Response::INFO_ORDER_CANCELED, MercadoPago_Core_Helper_Response::HTTP_BAD_REQUEST);

            return;
        }
        $this->_statusHelper->setStatusUpdated($this->_paymentData, $this->_order);
        if (!$this->_orderExists()) {
            return;
        }

        $this->_postStandardAction();
    }

    protected function _postStandardAction()
    {
        $this->_helper->log('Update Order', self::LOG_FILE);
        $this->getCore()->updateOrder($this->_order, $this->_paymentData);
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
        $this->_statusHelper = Mage::helper('mercadopago/statusUpdate');
        $this->_helper->log('Custom Received notification', self::LOG_FILE, $request->getParams());
        $dataId = $request->getParam('data_id');
        $type = $request->getParam('type');
        if (!empty($dataId) && $type == 'payment') {
            $response = $this->getCore()->getPaymentV1($dataId);
            $this->_helper->log('Return payment', self::LOG_FILE, $response);

            if ($this->_isValidResponse($response)) {
                $payment = $response['response'];

                $payment = $this->_helper->setPayerInfo($payment);
                $this->_order = Mage::getModel('sales/order')->loadByIncrementId($payment['external_reference']);
                if (!$this->_orderExists() || $this->_order->getStatus() == 'canceled') {
                    return;
                }
                $this->_helper->log('Update Order', self::LOG_FILE);
                $this->_statusHelper->setStatusUpdated($payment, $this->_order);

                $data = $this->_statusHelper->formatArrayPayment($data = array(), $payment, self::LOG_FILE);

                $this->getCore()->updateOrder($this->_order, $data);
                $setStatusResponse = $this->_statusHelper->setStatusOrder($payment);
                $this->_setResponse($setStatusResponse['body'], $setStatusResponse['code']);
                $this->_helper->log('Http code', self::LOG_FILE, $this->getResponse()->getHttpResponseCode());

                return;
            }
        }

        $this->_helper->log('Payment not found', self::LOG_FILE, $request->getParams());

        // Internal error returns to force Mercado Pago renotification
        $this->_setResponse('Payment not found', MercadoPago_Core_Helper_Response::HTTP_INTERNAL_ERROR);

        $this->_helper->log('Http code', self::LOG_FILE, $this->getResponse()->getHttpResponseCode());
    }

    protected function _handleMerchantOrder($id)
    {
        $merchantOrder = $this->getCore()->getMerchantOrder($id);
        $this->_helper->log('Return merchant_order', self::LOG_FILE, $merchantOrder);
        if (!$this->_isValidMerchantOrder($merchantOrder)) {
            $this->_helper->log(MercadoPago_Core_Helper_Response::INFO_MERCHANT_ORDER_NOT_FOUND, self::LOG_FILE, $this->_requestData);
            $this->_setResponse(MercadoPago_Core_Helper_Response::INFO_MERCHANT_ORDER_NOT_FOUND, MercadoPago_Core_Helper_Response::HTTP_NOT_FOUND);

            return false;
        }

        $this->_paymentData = $this->_getDataPayments();
        $this->_statusFinal = $this->_statusHelper->getStatusFinal($this->_paymentData['status'], $this->_merchantOrder);
        $this->_shipmentData = $this->_getShipmentsArray();
        $this->merchantOrder = $merchantOrder;

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

    protected function _getFormattedPaymentData($paymentId, $data = array())
    {
      
      $response = $this->getCore()->getPayment($paymentId);

      if (!$this->_isValidResponse($response)) {
        return array();
      }
      
      $payment = $response['response'];
      
      return $this->_statusHelper->formatArrayPayment($data, $payment, self::LOG_FILE);
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
        return (isset($this->_merchantOrder['shipments'][0])) ? $this->_merchantOrder['shipments'][0] : array();
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

    protected function _isValidResponse($response)
    {
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
        $this->_setResponse(MercadoPago_Core_Helper_Response::INFO_EXTERNAL_REFERENCE_NOT_FOUND, MercadoPago_Core_Helper_Response::HTTP_INTERNAL_ERROR);

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
                array('shipmentData' => $this->_shipmentData,
                 'orderId'      => $this->_merchantOrder['external_reference'])
            );
        }
    }

    protected function _dispatchNotificationEvent()
    {
        if ($this->_shipmentExists($this->_shipmentData)) {
            Mage::dispatchEvent('mercadopago_standard_notification_received',
                array('payment'        => $this->_paymentData,
                 'merchant_order' => $this->_merchantOrder)
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

    /**
     * @var $profile Mage_Sales_Model_Recurring_Profile
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function recurringAction()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['preapproval_id'])) {
            $preapprovalId = $params['preapproval_id'];
        } elseif (isset($params['id'])) {
            $preapprovalId = $params['id'];
        }

        $response = $this->getCore()->getRecurringPayment($preapprovalId);

        $profileId = $response ['response']['external_reference'];
        $newState = $response ['response']['status'];
        $newAmount = $response ['response']['auto_recurring']['transaction_amount'];

        $profile = Mage::getModel('sales/recurring_profile')->load($profileId);
        $actualState = $profile->getState();
        $actualAmount = $profile->getBillingAmount() + $profile->getShippingAmount();

        if ($actualState != $newState) {
            $state = null;
            switch ($newState) {
                case 'cancelled' :
                    $state = Mage_Sales_Model_Recurring_Profile::STATE_CANCELED;
                    break;
                case 'paused' :
                    $state = Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED;
                    break;
                case 'authorized' :
                    $state = Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE;
                    break;
            }
            $profile->setState($state);
            $profile->save();
        }

        if ($actualAmount != $newAmount) {
            $billingAmount = $newAmount - $profile->getShippingAmount();
            $profile->setBillingAmount($billingAmount);
            $profile->save();
        }

        return $this->_redirect();

    }

    public function recurringPaymentAction() {
        $params = $this->getRequest()->getParams();
        if (!isset($params['id'])) {
            return;
        }
        $paymentData = $this->getCore()->getPayment($params['id']);
        if (empty($paymentData) || ($paymentData['status'] != 200 && $paymentData['status'] != 201)) {
            return;
        }
        Mage::helper('mercadopago')->log('Recurring PaymentAction Data', self::LOG_FILE, $paymentData);
        $paymentData=$paymentData['response']['collection'];
        if ($paymentData['operation_type'] == 'recurring_payment' && $paymentData['status'] == 'approved') {
            $profile = Mage::getModel('sales/recurring_profile')->load($paymentData['external_reference']);
            if ($profile->getId()) {
                $item = new Varien_Object();
                $item->setData($profile->getOrderItemInfo());
                $item->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);
                $order = $profile->createOrder($item)->save();
                $statusHelper = Mage::helper('mercadopago/statusUpdate');
                if ($order->getId()) {
                    $paymentData = Mage::helper('mercadopago')->setPayerInfo($paymentData);
                    $statusHelper->setStatusUpdated($paymentData, $order);

                    $data = $this->_statusHelper->formatArrayPayment($data = array(), $paymentData, self::LOG_FILE);

                    $this->getCore()->updateOrder($order, $data);
                    Mage::helper('mercadopago/statusUpdate')->setStatusOrder($paymentData);
                    $profile->addOrderRelation($order->getId());
                }
            }
        }
    }

}
