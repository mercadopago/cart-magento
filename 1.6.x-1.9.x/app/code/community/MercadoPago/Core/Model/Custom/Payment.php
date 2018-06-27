<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Payment Gateway
 * @package   MercadoPago
 * @author    Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 * @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MercadoPago_Core_Model_Custom_Payment
    extends MercadoPago_Core_Model_CustomPayment
{
    //configura o block do formulario e de informações sobre o pagamento
    protected $_formBlockType = 'mercadopago/custom_form';
    protected $_infoBlockType = 'mercadopago/custom_info';

    protected $_code = 'mercadopago_custom';

    const LOG_FILE = 'mercadopago-custom.log';
    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';

    public static $exclude_inputs_opc = array('issuer_id', 'card_expiration_month', 'card_expiration_year', 'card_holder_name', 'doc_type', 'doc_number');

    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initialize($paymentAction, $stateObject)
    {

        if ($this->getInfoInstance()->getAdditionalInformation('token') == "") {
            Mage::throwException(Mage::helper('mercadopago')->__('Verify the form data or wait until the validation of the payment data'));
        }

        // $useTwoCards = $this->getInfoInstance()->getAdditionalInformation('is_second_card_used');
        //
        // if ($useTwoCards === "true") {
        //     $usingSecondCardInfo['first_card']['amount'] = $this->getInfoInstance()->getAdditionalInformation('first_card_amount');
        //     $usingSecondCardInfo['first_card']['installments'] = $this->getInfoInstance()->getAdditionalInformation('installments');
        //     $usingSecondCardInfo['first_card']['payment_method_id'] = $this->getInfoInstance()->getAdditionalInformation('payment_method');
        //     $usingSecondCardInfo['first_card']['token'] = $this->getInfoInstance()->getAdditionalInformation('token');
        //
        //     $usingSecondCardInfo['second_card']['amount'] = $this->getInfoInstance()->getAdditionalInformation('second_card_amount');
        //     $usingSecondCardInfo['second_card']['installments'] = $this->getInfoInstance()->getAdditionalInformation('second_card_installments');
        //     $usingSecondCardInfo['second_card']['payment_method_id'] = $this->getInfoInstance()->getAdditionalInformation('second_card_payment_method_id');
        //     $usingSecondCardInfo['second_card']['token'] = $this->getInfoInstance()->getAdditionalInformation('second_card_token');
        //
        //     $responseFirstCard = $this->preparePostPayment($usingSecondCardInfo['first_card']);
        //     if (isset($responseFirstCard) && ($responseFirstCard['response']['status'] == 'approved') ) {
        //         $paymentFirstCard = $responseFirstCard['response'];
        //         $responseSecondCard = $this->preparePostPayment($usingSecondCardInfo['second_card']);
        //
        //         if (isset($responseSecondCard) && ($responseSecondCard['response']['status'] == 'approved') ) {
        //             $paymentSecondCard = $responseSecondCard['response'];
        //             $this->getInfoInstance()->setAdditionalInformation('status', $paymentFirstCard['status'] . ' | ' . $paymentSecondCard['status']);
        //             $this->getInfoInstance()->setAdditionalInformation('payment_id_detail', $paymentFirstCard['id']  . ' | ' . $paymentSecondCard['id']);
        //             $this->getInfoInstance()->setAdditionalInformation('status_detail', $paymentFirstCard['status_detail'] . ' | ' . $paymentSecondCard['status_detail']);
        //             $this->getInfoInstance()->setAdditionalInformation('installments', $paymentFirstCard['installments'] . ' | ' . $paymentSecondCard['installments']);
        //             $this->getInfoInstance()->setAdditionalInformation('payment_method', $paymentFirstCard['payment_method_id'] . ' | ' . $paymentSecondCard['payment_method_id']);
        //             $this->getInfoInstance()->setAdditionalInformation('first_payment_id', $paymentFirstCard['id']);
        //             $this->getInfoInstance()->setAdditionalInformation('first_payment_status', $paymentFirstCard['status']);
        //             $this->getInfoInstance()->setAdditionalInformation('first_payment_status_detail', $paymentFirstCard['status_detail']);
        //             $this->getInfoInstance()->setAdditionalInformation('second_payment_id', $paymentSecondCard['id']);
        //             $this->getInfoInstance()->setAdditionalInformation('second_payment_status', $paymentSecondCard['status']);
        //             $this->getInfoInstance()->setAdditionalInformation('second_payment_status_detail', $paymentSecondCard['status_detail']);
        //             $this->getInfoInstance()->setAdditionalInformation('total_paid_amount', $paymentFirstCard['transaction_details']['total_paid_amount'] . '|' . $paymentSecondCard['transaction_details']['total_paid_amount']);
        //             $this->getInfoInstance()->setAdditionalInformation('transaction_amount', $paymentFirstCard['transaction_amount'] . '|' . $paymentSecondCard['transaction_amount']);
        //             $this->getInfoInstance()->setAdditionalInformation('payer_identification_type', $paymentFirstCard['payer']['identification']['type']. '|' . $paymentSecondCard['payer']['identification']['type']);
        //             $this->getInfoInstance()->setAdditionalInformation('payer_identification_number', $paymentFirstCard['payer']['identification']['number'] . '|' . $paymentSecondCard['payer']['identification']['number']);
        //
        //             $stateObject->setState(Mage::helper('mercadopago/statusUpdate')->_getAssignedState('pending_payment'));
        //             $stateObject->setStatus('pending_payment');
        //             $stateObject->setIsNotified(false);
        //             $this->saveOrder();
        //             return true;
        //         } else {
        //             //second card payment failed, refund for first card
        //             $accessToken = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        //             $mp = Mage::helper('mercadopago')->getApiInstance($accessToken);
        //             $id = $paymentFirstCard['id'];
        //             $refundResponse = $mp->post("/v1/payments/$id/refunds?access_token=$accessToken");
        //             Mage::helper('mercadopago')->log("info form", self::LOG_FILE, $refundResponse);
        //             return false;
        //         }
        //     } else {
        //         return false;
        //     }
        //
        //
        // } else {

        $response = $this->preparePostPayment();

        if ($response) {
            $payment = $response['response'];
            //set status
            $this->getInfoInstance()->setAdditionalInformation('status', $payment['status']);
            $this->getInfoInstance()->setAdditionalInformation('payment_id_detail', $payment['id']);
            $this->getInfoInstance()->setAdditionalInformation('status_detail', $payment['status_detail']);
          
            if(isset($payment['payer']) && isset($payment['payer']['identification']) && isset($payment['payer']['identification']['type'])){
              $this->getInfoInstance()->setAdditionalInformation('payer_identification_type', $payment['payer']['identification']['type']);
            }

            if(isset($payment['payer']) && isset($payment['payer']['identification']) && isset($payment['payer']['identification']['number'])){
              $this->getInfoInstance()->setAdditionalInformation('payer_identification_number', $payment['payer']['identification']['number']);
            }

            $stateObject->setState(Mage::helper('mercadopago/statusUpdate')->_getAssignedState('pending_payment'));
            $stateObject->setStatus('pending_payment');
            $stateObject->setIsNotified(false);

            $this->saveOrder();

            return true;
        }
        // }

        return false;
    }

    protected function saveOrder() {
        $quote = $this->_getQuote();
        $order_id = $quote->getReservedOrderId();
        $order = $this->_getOrder($order_id);
        $order->save();
    }


    protected function cleanFieldsOcp($info)
    {
        foreach (self::$exclude_inputs_opc as $field) {
            $info[$field] = '';
        }

        return $info;
    }

    public function assignData($data)
    {

        // route /checkout/onepage/savePayment
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info_form = $data->getData();
        if(!isset($info_form['mercadopago_custom'])){
          return $this;
        }

        $info_form = $info_form['mercadopago_custom'];
        if (isset($info_form['CustomerAndCard']) && $info_form['CustomerAndCard'] == 1) {
            $info_form = $this->cleanFieldsOcp($info_form);
        }

        if (empty($info_form['token'])) {
            $exception = new MercadoPago_Core_Model_Api_V1_Exception();
            $exception->setMessage($exception->getUserMessage());
            throw $exception;
        }

        //Added to force value as there are cases coming -1
        if( $info_form['installments'] == -1 && strtoupper(Mage::getStoreConfig('payment/mercadopago/country')) == "MLV"){
            $info_form['installments'] = 1;
            Mage::helper('mercadopago')->log("Installment updated to 1... form return -1. ", self::LOG_FILE);
        }

        Mage::helper('mercadopago')->log("info form", self::LOG_FILE, $info_form);
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation($info_form);
        $info->setAdditionalInformation('payment_type_id', "credit_card");
        if ((!empty($info_form['cardExpirationMonth']) && !empty($info_form['cardExpirationYear'])) && ($info_form['cardExpirationMonth'] != -1 && $info_form['cardExpirationYear'] != -1)) {
            $info->setAdditionalInformation('expiration_date', $info_form['cardExpirationMonth'] . "/" . $info_form['cardExpirationYear']);
        }
        $info->setAdditionalInformation('payment_method', $info_form['paymentMethodId']);
        $info->setAdditionalInformation('cardholderName', $info_form['cardholderName']);

        //@Gateway_Mode
        if (isset($info_form['gateway_mode'])) {
            $info->setAdditionalInformation('gateway_mode', $info_form['gateway_mode']);
        }

        return $this;
    }


    public function getDiscount()
    {
        $discount = 0;
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();

        if (isset($totals['discount']) && $totals['discount']->getValue()) {
            $discount = $totals['discount']->getValue();
        }

        return $discount;
    }

    protected function getPaymentInfo($payment)
    {
        $payment_info = array();

        if ($payment->getAdditionalInformation("coupon_code") != "") {
            $payment_info['coupon_code'] = $payment->getAdditionalInformation("coupon_code");
        }

        if ($payment->getAdditionalInformation("doc_number") != "") {
            $payment_info['identification_type'] = $payment->getAdditionalInformation("doc_type");
            $payment_info['identification_number'] = $payment->getAdditionalInformation("doc_number");
        }

        return $payment_info;
    }

    public function preparePostPayment($usingSecondCardInfo = null)
    {
        //check actual time
        $init = microtime(true);
      
        Mage::helper('mercadopago')->log("Credit Card -> init prepare post payment", self::LOG_FILE);
        $core = Mage::getModel('mercadopago/core');
        $quote = $this->_getQuote();
        $order_id = $quote->getReservedOrderId();
        $order = $this->_getOrder($order_id);

        $payment = $order->getPayment();
        $payment_info = $this->getPaymentInfo($payment);

        if (isset($usingSecondCardInfo)) {
            $payment_info['transaction_amount'] = $usingSecondCardInfo ['amount'];
        }

        $preference = $core->makeDefaultPreferencePaymentV1($payment_info);

        if (isset($usingSecondCardInfo)) {
            $preference['installments'] = (int)$usingSecondCardInfo['installments'];
            $preference['payment_method_id'] = $usingSecondCardInfo['paymentMethodId'];
            $preference['token'] = $usingSecondCardInfo['token'];
        } else {
            $preference['installments'] = (int)$payment->getAdditionalInformation("installments");
            $preference['payment_method_id'] = $payment->getAdditionalInformation("payment_method");
            $preference['token'] = $payment->getAdditionalInformation("token");
        }

        if ($payment->getAdditionalInformation("issuer_id") != "" && $payment->getAdditionalInformation("issuer_id") != -1) {
            $preference['issuer_id'] = (int)$payment->getAdditionalInformation("issuer_id");
        }

        if ($payment->getAdditionalInformation("customer_id") != "") {
            $preference['payer']['id'] = $payment->getAdditionalInformation("customer_id");

            $preference['metadata']['token'] = $payment->getAdditionalInformation("token");
            $preference['metadata']['customer_id'] = $payment->getAdditionalInformation("customer_id");
        }

        //@Gateway_Mode
        if ($payment->getAdditionalInformation("gateway_mode") != "") {
            $preference['processing_mode'] = 'gateway';
        }

        $preference['binary_mode'] = Mage::getStoreConfigFlag('payment/mercadopago_custom/binary_mode');
        $preference['statement_descriptor'] = Mage::getStoreConfig('payment/mercadopago_custom/statement_descriptor');

        Mage::helper('mercadopago')->log("Credit Card -> PREFERENCE to POST /v1/payments", self::LOG_FILE, $preference);

        /* POST /v1/payments */
        $response = $core->postPaymentV1($preference);
      
        //calculate time consumed
        $timeConsumed = round(microtime(true) - $init, 3); 
        Mage::helper('mercadopago')->log("Time consumed to create payment (credit card): " . $timeConsumed . "s", 'mercadopago-custom.log');
      
        return $response;
    }

    public function customerAndCards($token, $payment_created)
    {
        $customer = $this->getOrCreateCustomer($payment_created['payer']['email']);

        if ($customer !== false) {
            $this->checkAndcreateCard($customer, $token, $payment_created);
        }
    }

    public function getOrCreateCustomer($email)
    {
        if (empty($email)) {
            return false;
        }
        $access_token = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);

        $mp = Mage::helper('mercadopago')->getApiInstance($access_token);

        $customer = $mp->get("/v1/customers/search", array("email" => $email));

        Mage::helper('mercadopago')->log("Response search customer", self::LOG_FILE, $customer);

        if ($customer['status'] == 200) {

            if ($customer['response']['paging']['total'] > 0) {
                return $customer['response']['results'][0];
            } else {
                Mage::helper('mercadopago')->log("Customer not found: " . $email, self::LOG_FILE);

                $customer = $mp->post("/v1/customers", array("email" => $email));

                Mage::helper('mercadopago')->log("Response create customer", self::LOG_FILE, $customer);

                if ($customer['status'] == 201) {
                    return $customer['response'];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function checkAndcreateCard($customer, $token, $payment)
    {
        $access_token = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);

        $mp = Mage::helper('mercadopago')->getApiInstance($access_token);

        foreach ($customer['cards'] as $card) {


            if ($card['first_six_digits'] == $payment['card']['first_six_digits']
                && $card['last_four_digits'] == $payment['card']['last_four_digits']
                && $card['expiration_month'] == $payment['card']['expiration_month']
                && $card['expiration_year'] == $payment['card']['expiration_year']
            ) {
                Mage::helper('mercadopago')->log("Card already exists", self::LOG_FILE, $card);

                return $card;
            }
        }

        $params = array("token" => $token);

        if (isset($payment['issuer_id'])) {
            $params['issuer_id'] = (int)$payment['issuer_id'];
        }
        if (isset($payment['payment_method_id'])) {
            $params['payment_method_id'] = $payment['payment_method_id'];
        }
        $card = $mp->post("/v1/customers/" . $customer['id'] . "/cards", $params);

        Mage::helper('mercadopago')->log("Response create card", self::LOG_FILE, $card);

        if ($card['status'] == 201) {
            return $card['response'];
        }

        return false;
    }

    public function getCustomerAndCards()
    {
        $email = Mage::getModel('mercadopago/core')->getEmailCustomer();

        $customer = $this->getOrCreateCustomer($email);

        return $customer;
    }


    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('mercadopago/checkout/page', array('_secure' => true));
    }

    public function getCode()
    {
        return $this->_code;
    }

}
