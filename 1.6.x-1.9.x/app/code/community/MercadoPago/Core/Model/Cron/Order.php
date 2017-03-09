<?php


class MercadoPago_Core_Model_Cron_Order
{

    public function updateOrderStatus(){
        $statusHelper = Mage::helper('mercadopago/statusUpdate');
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
//        $collection2 = Mage::getModel('sales/order')->getCollection()
//            ->addFieldToSelect('*')
//            ->addFieldToFilter('payment.method',array('in' => array(
//                'mercadopago_custom',
//                'mercadopago_customticket',
//                'mercadopago_standard')))

//
//        //Verificar que los elementos tengan el metodo de pago MP...
//        $order_collection = Mage::getModel('sales/order')->getCollection()
////                                                            ->addFieldToFilter('status', array('nin' => array('canceled','complete')))
//        ;

        //otra alternativa (por si no muestra el codigo laversion anterior
//        $order = Mage::getModel("sales/order")->getCollection()
//                                                ->addAttributeToSelect('*')
//                                                ->addAttributeToFilter('status', array('nin' => array('canceled','complete')))
////                                                ->addAttributeToFilter('status', array('nin' => array('canceled','complete')))
//                                                ;

        /*
         * For OrderCollection.
         *      get code
         *      setea el status desde la config
         *      guarda la orden modificada
         *
         * */

//        $ordersByPaymentMP = Mage::getResourceModel('sales/order_payment_collection')
//            ->addFieldToSelect('*')
//            ->addFieldToFilter('method',array('in' => array(
//                                                            'mercadopago_custom',
//                                                            'mercadopago_customticket',
//                                                            'mercadopago_standard')))
//            ;

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
                    $statusFinal = $statusHelper->getStatusFinal($paymentData['status'], $merchantOrderData);
                    $statusDetail = $infoPayments['status_detail'];

                    $statusOrder = $statusHelper->getStatusOrder($statusFinal, $statusDetail);
                    if (isset($statusOrder) && ($order->getStatus() !== $statusOrder)) {
                        $order->setState($statusHelper->_getAssignedState($statusOrder));
                        $order->addStatusToHistory($statusOrder, $statusHelper->getMessage($statusOrder, $paymentOrder), true);
                        $order->sendOrderUpdateEmail(true, $statusHelper->getMessage($statusOrder, $paymentOrder));
                        $order->save();
                    }
                } else{
                    $helper->log('Error updating status order using cron whit the merchantOrder num: '. $merchantOrderId .'mercadopago.log');
                }
            }
        }
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
