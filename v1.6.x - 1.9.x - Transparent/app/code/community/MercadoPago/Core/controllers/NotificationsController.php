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

    protected function getDataPayments($merchantOrder) {
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
        $statuses = explode('|',$dataStatus);
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
                    $data = $this->getDataPayments($merchant_order);
                    $status_final = $this->getStatusFinal($data['status']);

                    $this->updateOrder($data);

                    if ($status_final != false) {
                        $data['status_final'] = $status_final;
                        $this->setStatusOrder($data);
                    }
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
        Mage::helper('mercadopago')->log("Custom Received notification", 'mercadopago-notification.log',  $request->getParams());

        $core = Mage::getModel('mercadopago/core');

        $dataId = $request->getParam('data_id');
        $type = $request->getParam('type');
        if (!empty($dataId) && $type == 'payment') {
            $response = $core->getPaymentV1($dataId);
            Mage::helper('mercadopago')->log("Return payment", 'mercadopago-notification.log', $response);

            if ($response['status'] == 200 || $response['status'] == 201) {
                $payment = $response['response'];

                //Atualiza informações da order
                $payment["trunc_card"] = "xxxx xxxx xxxx " . $payment['card']["last_four_digits"];
                $payment["cardholder_name"] = $payment['card']["cardholder"]["name"];
                $payment['payer_first_name'] = $payment['payer']['first_name'];
                $payment['payer_last_name'] = $payment['payer']['last_name'];
                $payment['payer_email'] = $payment['payer']['email'];

                $this->updateOrder($payment);

                //atualiza status da order de acordo com a notificação do pagamento
                $this->setStatusOrder($payment);

                //forca return
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
                if (isset($data[$field])){
                    $payment_order->setAdditionalInformation($field,$data[$field]);
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

            $status_save = $order->save();
            Mage::helper('mercadopago')->log("Update order", 'mercadopago-notification.log', $status_save->toString());
        } catch (Exception $e) {
            Mage::helper('mercadopago')->log("erro in update order status: " . $e, 'mercadopago-notification.log');
            $this->getResponse()->setBody($e);

            //caso erro no processo de notificação de pagamento, mercadopago ira notificar novamente.
            $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_BAD_REQUEST);
        }
    }


    public function setStatusOrder($payment)
    {
        try {
            Mage::helper('mercadopago')->log("Received Payment data", 'mercadopago-notification.log', $payment);

            // obtem a order para atualizar o status
            $order = Mage::getModel('sales/order')->loadByIncrementId($payment["external_reference"]);

            //adiciona variavel de status
            $status = $payment['status'];

            //caso exista um status_final, utiliza ele (pagamento com dois cartões)
            if (isset($payment['status_final'])) {
                $status = $payment['status_final'];
            }

            switch ($status) {
                case 'approved':
                    //add status na order
                    $message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment was approved.');
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_approved');

                    //cria a invoice
                    $invoice = $order->prepareInvoice();
                    $invoice->register()->pay();
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();

                    $invoice->sendEmail(true, $message);
                    break;

                case 'refunded':
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_refunded');
                    $message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment was refunded.');
                    $order->cancel();
                    break;

                case 'pending':
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
                    $message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment is being processed.');
                    break;

                case 'authorized':
                case 'in_process':
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
                    $message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment is being processed. Will be approved within 2 business days.');
                    break;

                case 'in_mediation':
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_mediation');
                    $message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment is in the process of Dispute, check the graphic account of the MercadoPago for more information.');
                    break;

                case 'cancelled':
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_cancelled');
                    $message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment was cancelled.');
                    $order->cancel();
                    break;

                case 'rejected':
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_rejected');
                    $message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment was rejected.');
                    break;

                case 'chargeback':
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_chargeback');
                    $message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: One chargeback was initiated for this payment.');
                    break;

                default:
                    $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
                    $message = '';
            }

            //adiciona informações do pagamento para enviar por email e salvar nos historicos
            $message .= Mage::helper('mercadopago')->__('<br/> Payment id: %s', $payment['id']);
            $message .= Mage::helper('mercadopago')->__('<br/> Status: %s', $payment['status']);
            $message .= Mage::helper('mercadopago')->__('<br/> Status Detail: %s', $payment['status_detail']);

            $order->addStatusToHistory($status, $message, true);
            $order->sendOrderUpdateEmail(true, $message);

            $status_save = $order->save();
            Mage::helper('mercadopago')->log("Update order", 'mercadopago-notification.log', $status_save->toString());
            Mage::helper('mercadopago')->log($message, 'mercadopago-notification.log');

            $this->getResponse()->setBody($message);
        } catch (Exception $e) {
            Mage::helper('mercadopago')->log("erro in set order status: " . $e, 'mercadopago-notification.log');
            $this->getResponse()->setBody($e);

            //caso erro no processo de notificação de pagamento, mercadopago ira notificar novamente.
            $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_BAD_REQUEST);
        }
    }

    /*
    * Funcao responsavel por formatar o array para atualizar informações do pedido
    */

    public function formatArrayPayment($data, $payment)
    {
        Mage::helper('mercadopago')->log("Format Array", 'mercadopago-notification.log');

        $fields = array(
            "status",
            "status_detail",
            "id",
            "payment_method_id",
            "transaction_amount",
            "installments"
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


        //esses dados não precisam concatenar pois se repetem..
        $data['external_reference'] = $payment['external_reference'];
        $data['payer_first_name'] = $payment['payer']['first_name'];
        $data['payer_last_name'] = $payment['payer']['last_name'];
        $data['payer_email'] = $payment['payer']['email'];

        return $data;
    }
}
