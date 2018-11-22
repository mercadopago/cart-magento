<?php

class MercadoPago_Core_Helper_StatusUpdate
    extends Mage_Payment_Helper_Data
{

    protected $_statusUpdatedFlag = false;

    /***
     * @var Mage_Sales_Model_Order
     */
    protected $_order = false;

    protected $_finalStatus = array('rejected', 'cancelled', 'refunded', 'charge_back');
    protected $_notFinalStatus = array('authorized', 'process', 'in_mediation');

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
      
        $lastStatus = str_replace(' ', '', array_pop($statuses));
      
        return $lastStatus;
    }

    protected function _updateStatus($status, $message, $statusDetail, $payment_data = null)
    {
        if ($this->_order->getState() !== Mage_Sales_Model_Order::STATE_COMPLETE) {            
          
            //use status final when is payment with two cards
            if(!is_null($payment_data) && isset($payment_data['status_final'])){
              $status = $payment_data['status_final'];
            }
          
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
        $items = $item->getItems();
      
        return array_pop($items)->getState();
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
        //actual status != final_status
        $status = $this->getStatus($payment);
        
        $message = $this->getMessage($status, $payment);

        if ($this->isStatusUpdated()) {
            if (!(isset($payment['amount_refunded']) && ($payment['amount_refunded'] > 0))) {
                if (!(isset($payment['refunds']) && count($payment['refunds']) > 0)) {
                    if ($this->_order->getPayment()->getAdditionalInformation('is_second_card_used')) {
                        //if status is updated, there are no refunds and no custom payments with two cards.
                        return array('body' => $message, 'code' => MercadoPago_Core_Helper_Response::HTTP_OK);
                    }
                }
            }
        }

        try {
            $statusSave = $this->update($payment, $message);

            $helper->log("Update order", 'mercadopago.log', $statusSave->getData());
            $helper->log($message, 'mercadopago.log');

            return array('body' => $message, 'code' => MercadoPago_Core_Helper_Response::HTTP_OK);
        } catch (Exception $e) {
            $helper->log("error in set order status: " . $e, 'mercadopago.log');

            return array('body' => $e, 'code' => MercadoPago_Core_Helper_Response::HTTP_BAD_REQUEST);
        }
    }

    public function update($payment, $message)
    {
      
      $statusDetail = $payment['status_detail'];
      $status = $payment['status'];

      //define status final if exist
      if(isset($payment['status_final']) && $payment['status_final'] != ""){
        $status = $payment['status_final'];
      }

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
        $this->_updateStatus($status, $message, $statusDetail, $payment);
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
            case 'in_process': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
                break;
            }
            default: {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_pending');
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
        $dates = array();
        foreach ($payments as $key => $payment) {
            if (in_array($payment['status'], $status)) {
                $dates[] = array('key' => $key, 'value' => $payment['last_modified']);
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

      if(isset($merchantOrder['payments']) && count($merchantOrder['payments']) == 1){
        return $merchantOrder['payments'][0]['status'];
      }

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

    /**
     * @param $data
     * @param $payment
     * @param $logFile
     *
     * Update $data with the information in the payment.
     * if it has more than one payment, the information is concatenated by separating the data with a '|'
     *
     * @return mixed
     */
    public function formatArrayPayment($data, $payment, $logFile)
    {
        Mage::helper('mercadopago')->log("Format Array", $logFile);

        $fields = array(
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
            "shipping_amount",
            "amount_refunded"
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
        $data = $this->_updateAtributesData($data, $payment);

        $data['external_reference'] = $payment['external_reference'];
        $data['payer_first_name'] = $payment['payer']['first_name'];
        $data['payer_last_name'] = $payment['payer']['last_name'];
        $data['payer_email'] = $payment['payer']['email'];
                
        if(isset($payment['payer']) && isset($payment['payer']['identification']) && isset($payment['payer']['identification']['type'])){
          if (isset($data['payer_identification_type'])) {
              $data['payer_identification_type'] .= " | " . $payment['payer']['identification']['type'];
          } else {
              $data['payer_identification_type'] = $payment['payer']['identification']['type'];
          }
        }

        if(isset($payment['payer']) && isset($payment['payer']['identification']) && isset($payment['payer']['identification']['number'])){      
          if (isset($data['payer_identification_number']))  {
              $data['payer_identification_number'] .= " | " . $payment['payer']['identification']['number'];
          } else {
              $data['payer_identification_number'] = $payment['payer']['identification']['number'];
          }
        }

        return $data;
    }

    protected function _updateAtributesData($data, $payment){
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

        if(isset($payment['order']) && isset($payment['order']['type']) && $payment['order']['type'] == 'mercadopago' ){
            $data['merchant_order_id'] = $payment['order']['id'];
        }

        return $data;
    }

    public function getDataPayments($merchantOrderData, $logFile)
    {
        $data = array();
        foreach ($merchantOrderData['payments'] as $payment) {
            $data = $this->_getFormattedPaymentData($payment['id'], $data, $logFile);
        }

        return $data;
    }

    protected function _getFormattedPaymentData($paymentId, $data = array(), $logFile)
    {
        $core = Mage::getModel('mercadopago/core');

        $response = $core->getPayment($paymentId);
        if ($response['status'] == 400 || $response['status'] == 401) {
            return array();
        }
        $payment = $response['response']['collection'];

        return $this->formatArrayPayment($data, $payment, $logFile);
    }

}
