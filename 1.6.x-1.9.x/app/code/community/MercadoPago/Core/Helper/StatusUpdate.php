<?php

class MercadoPago_Core_Helper_StatusUpdate
    extends Mage_Payment_Helper_Data
{

    protected $_statusUpdatedFlag = false;

    /***
     * @var Mage_Sales_Model_Order
     */
    protected $_order = false;

    protected $_finalStatus = ['rejected', 'cancelled', 'refunded', 'charge_back'];
    protected $_notFinalStatus = ['authorized', 'process', 'in_mediation'];

    private $_rawMessage;

    public function isStatusUpdated()
    {
        return $this->_statusUpdatedFlag;
    }

    public function setStatusUpdated($notificationData, $order, $isPayment = false)
    {
        $this->_order = $order;
        $status = $notificationData['status'];
        $statusDetail = $notificationData['status_detail'];
        $currentStatus = $this->_order->getPayment()->getAdditionalInformation('status');
        $currentStatusDetail = $this->_order->getPayment()->getAdditionalInformation('status_detail');
        if ($isPayment) {
            $currentStatus = $this->_getMulticardLastValue($currentStatus);
            $currentStatusDetail = $this->_getMulticardLastValue($currentStatusDetail);
        }
        if (!is_null($order->getPayment()) && $order->getPayment()->getAdditionalInformation('is_second_card_used')) {
            $this->_statusUpdatedFlag = false;

            return;
        }
        if ($status == $currentStatus && $statusDetail == $currentStatusDetail) {
            $this->_statusUpdatedFlag = true;
        }
    }

    protected function _getMulticardLastValue($value)
    {
        $statuses = explode('|', $value);

        return str_replace(' ', '', array_pop($statuses));
    }

    protected function _updateStatus($status, $message, $statusDetail)
    {
        if ($this->_order->getState() !== Mage_Sales_Model_Order::STATE_COMPLETE
        ) {
            $statusOrder = $this->getStatusOrder($status, $statusDetail);

            if (isset($statusOrder) && ($this->_order->getStatus() !== $statusOrder)) {
                $this->_order->setState($this->_getAssignedState($statusOrder));
                $this->_order->addStatusToHistory($statusOrder, $message, true);
                $this->_order->sendOrderUpdateEmail(true, $message);
            }
        }
    }

    /**
     * Get the assigned state of an order status
     *
     * @param string $status
     */
    public function _getAssignedState($status)
    {
        $item = Mage::getResourceModel('sales/order_status_collection')
            ->joinStates()
            ->addFieldToFilter('main_table.status', $status);

        return array_pop($item->getItems())->getState();
    }

    protected function _generateCreditMemo($payment)
    {
        if ($payment['amount_refunded'] == $payment['total_paid_amount']) {
            $this->_createCreditmemo($payment);
            $this->_order->setForcedCanCreditmemo(false);
            $this->_order->setActionFlag('ship', false);
            $this->_order->save();
        } else {
            $this->_createCreditmemo($payment);
        }
    }

    protected function _createCreditmemo($data)
    {
        /**
         * @var $creditmemo Mage_Sales_Model_Order_Creditmemo
         */
        $this->_order->setExternalRequest(true);
        $serviceModel = Mage::getModel('sales/service_order', $this->_order);
        $baseGrandTotal = $this->_order->getBaseGrandTotal();
        $invoice = array_pop($this->_order->getInvoiceCollection()->setPageSize(1)->setCurPage(1)->load()->getItems());

        $creditMemos = $this->_order->getCreditmemosCollection()->getItems();

        $previousRefund = 0;
        foreach ($creditMemos as $creditMemo) {
            $previousRefund = $previousRefund + $creditMemo->getGrandTotal();
        }

        $amount = $data['amount_refunded'] - $previousRefund;
        if ($amount > 0) {
            if (count($creditMemos) == 0) {
                $adjustment = array('adjustment_positive' => $amount);
            } else {
                $adjustment = array('adjustment_negative' => 0 - $amount);
            }
            $adjustment['qtys'] = -1;
            $creditmemo = $serviceModel->prepareInvoiceCreditmemo($invoice, $adjustment);
            if ($creditmemo) {
                $totalRefunded = $invoice->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal();
                $this->_order->setShouldCloseParentTransaction($invoice->getBaseGrandTotal() <= $totalRefunded);
            }

            if ($data['amount_refunded'] == $baseGrandTotal) {
                $this->_order->setExternalType('total');
                $this->_order->getPayment()->refund($creditmemo);
            } else {
                $this->_order->setExternalType('partial');
            }
            $creditmemo->refund();
            Mage::getModel('core/resource_transaction')
                ->addObject($creditmemo)
                ->addObject($this->_order)
                ->save();
        }
    }

    public function setStatusOrder($payment)
    {
        $helper = Mage::helper('mercadopago');
        $status = $this->getStatus($payment);

        $message = $this->getMessage($status, $payment);
        if ($this->isStatusUpdated()) {
            if (!(isset($payment['amount_refunded']) && ($payment['amount_refunded'] > 0))) {
                if (!(isset($payment['refunds']) && count($payment['refunds']) > 0)) {
                    if ($this->_order->getPayment()->getAdditionalInformation('is_second_card_used')) {
                        //if status is updated, there are no refunds and no custom payments with two cards.
                        return ['body' => $message, 'code' => MercadoPago_Core_Helper_Response::HTTP_OK];
                    }
                }
            }
        }

        try {
            $statusSave = $this->update($payment, $message);

            $helper->log("Update order", 'mercadopago.log', $statusSave->getData());
            $helper->log($message, 'mercadopago.log');

            return ['body' => $message, 'code' => MercadoPago_Core_Helper_Response::HTTP_OK];
        } catch (Exception $e) {
            $helper->log("error in set order status: " . $e, 'mercadopago.log');

            return ['body' => $e, 'code' => MercadoPago_Core_Helper_Response::HTTP_BAD_REQUEST];
        }
    }

    public function update($payment, $message)
    {
        $statusDetail = $payment['status_detail'];
        $status = $payment['status'];
        $infoPayments = $this->_order->getPayment()->getAdditionalInformation();
        if ($this->_getMulticardLastValue($status) == 'approved') {
            $this->_handleTwoCards($payment, $infoPayments);

            Mage::helper('mercadopago')->setOrderSubtotals($payment, $this->_order);
            $this->_createInvoice($this->_order, $message);
            //Associate card to customer
            $additionalInfo = $this->_order->getPayment()->getAdditionalInformation();
            if (isset($additionalInfo['token'])) {
                Mage::getModel('mercadopago/custom_payment')->customerAndCards($additionalInfo['token'], $payment);
            }
        }

        if (isset($infoPayments['first_payment_id']) &&
            !($infoPayments['first_payment_status'] == 'approved' && $infoPayments['second_payment_status'] == 'approved')
        ) {
            return $this->_order->save();
        }

        if (isset($payment['amount_refunded']) && $payment['amount_refunded'] > 0) {
            $this->_generateCreditMemo($payment);
        } elseif ($status == 'cancelled') {
            Mage::register('mercadopago_cancellation', true);
            $this->_order->cancel();
        } else {
            //if state is not complete updates according to setting
            $this->_updateStatus($status, $message, $statusDetail);
        }

        return $this->_order->save();
    }

    protected function _handleTwoCards(&$payment, $infoPayments)
    {
        if (isset($infoPayments['is_second_card_used']) && $infoPayments['is_second_card_used'] === "true") {
            $payment['total_paid_amount'] = $infoPayments['total_paid_amount'];
            $payment['transaction_amount'] = $infoPayments['transaction_amount'];
            $payment['status'] = $infoPayments['status'];
        }
    }

    public function getMessage($status, $payment)
    {
        if (!$this->_rawMessage) {
            $rawMessage = Mage::helper('mercadopago')->__(Mage::helper('mercadopago/statusOrderMessage')->getMessage($status));
            $rawMessage .= Mage::helper('mercadopago')->__('<br/> Payment id: %s', $payment['id']);
            $rawMessage .= Mage::helper('mercadopago')->__('<br/> Status: %s', $payment['status']);
            $rawMessage .= Mage::helper('mercadopago')->__('<br/> Status Detail: %s', $payment['status_detail']);
            $this->_rawMessage = $rawMessage;
        }

        return $this->_rawMessage;
    }

    public function getStatus($payment)
    {
        $status = $payment['status'];
        if (isset($payment['status_final'])) {
            $status = $payment['status_final'];
        }

        return $status;
    }


    public function getStatusOrder($status, $statusDetail)
    {
        switch ($this->_getMulticardLastValue($status)) {
            case 'approved': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_approved');

                if ($statusDetail == 'partially_refunded' && $this->_order->canCreditMemo()) {
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_partially_refunded');
                }
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
    public function getStatusFinal($dataStatus, $merchantOrder)
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

    protected function _createInvoice($order, $message)
    {
        if (!$order->hasInvoices()) {
            $invoice = $order->prepareInvoice();
            $invoice->register();
            $invoice->pay();
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $invoice->sendEmail(true, $message);
        }
    }

}
