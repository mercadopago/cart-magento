<?php

class MercadoPago_Core_Model_Cron_Order
{
    protected $_statusHelper;

    public function updateOrderStatus(){
        $this->_statusHelper = Mage::helper('mercadopago/statusUpdate');
        $helper = Mage::helper('mercadopago');
        $hours = Mage::getStoreConfig('payment/mercadopago/number_of_hours');

        // filter to date:
        $fromDate = date('Y-m-d H:i:s', strtotime('-'.$hours. ' hours'));
        $toDate = date('Y-m-d H:i:s', strtotime("now"));

        $collection = Mage::getModel('sales/order')->getCollection()
            ->join(
                array('payment' => 'sales/order_payment'),
                'main_table.entity_id=payment.parent_id',
                array('payment_method' => 'payment.method')
            )->addFieldToFilter('payment.method', array('in' => array(
                    'mercadopago_custom',
                    'mercadopago_customticket',
                    'mercadopago_standard'))
            )->addFieldToFilter('status', array('nin' => array(
                    'canceled',
                    'complete'))
            )->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
        ;

        // For all Orders to analyze
        foreach($collection as $orderByPayment){
            $order = $orderByPayment;
            $paymentOrder = $order->getPayment();
            $infoPayments = $paymentOrder->getAdditionalInformation();

            if (isset($infoPayments['merchant_order_id']) && $order->getStatus() !== 'complete') {


                $merchantOrderId =  $infoPayments['merchant_order_id'];
                $response = Mage::getModel('mercadopago/core')->getMerchantOrder($merchantOrderId);

                if ($response['status'] == 201 || $response['status'] == 200) {
                    $merchantOrderData = $response['response'];

                    $paymentData = $this->getDataPayments($merchantOrderData);
                    $statusFinal = $this->_statusHelper->getStatusFinal($paymentData['status'], $merchantOrderData);
                    $statusDetail = $infoPayments['status_detail'];

                    $statusOrder = $this->_statusHelper->getStatusOrder($statusFinal, $statusDetail);
                    if (isset($statusOrder) && ($order->getStatus() !== $statusOrder)) {
                        $this->_updateOrder($order, $statusOrder, $paymentOrder);

                    }
                } else{
                    $helper->log('Error updating status order using cron whit the merchantOrder num: '. $merchantOrderId .'mercadopago.log');
                }
            }
        }
    }

    protected function _updateOrder($order, $statusOrder, $paymentOrder){
        $order->setState($this->_statusHelper->_getAssignedState($statusOrder));
        $order->addStatusToHistory($statusOrder, $this->_statusHelper->getMessage($statusOrder, $statusOrder), true);
        $order->sendOrderUpdateEmail(true, $this->_statusHelper->getMessage($statusOrder, $paymentOrder));
        $order->save();
    }

    protected function getDataPayments($merchantOrderData)
    {
        $data = array();
        foreach ($merchantOrderData['payments'] as $payment) {
            $data = $this->getFormattedPaymentData($payment['id'], $data);
        }

        return $data;
    }

    protected function getFormattedPaymentData($paymentId, $data = [])
    {
        $core = Mage::getModel('mercadopago/core');

        $response = $core->getPayment($paymentId);
        if ($response['status'] == 400 || $response['status'] == 401) {
            return [];
        }
        $payment = $response['response']['collection'];

        return $this->formatArrayPayment($data, $payment);
    }

    public function formatArrayPayment($data, $payment)
    {

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
            "amount_refunded"
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
