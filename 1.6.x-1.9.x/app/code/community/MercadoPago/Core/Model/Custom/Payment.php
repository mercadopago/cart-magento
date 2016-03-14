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

    public static $exclude_inputs_opc = ['issuer_id', 'card_expiration_month', 'card_expiration_year', 'card_holder_name', 'doc_type', 'doc_number'];

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

        $response = $this->preparePostPayment();

        if ($response !== false):

            $payment = $response['response'];
            //set status
            $this->getInfoInstance()->setAdditionalInformation('status', $payment['status']);
            $this->getInfoInstance()->setAdditionalInformation('status_detail', $payment['status_detail']);

            if ($response['status'] == 200 || $response['status'] == 201) {
                Mage::helper('mercadopago')->log("Received Payment data", self::LOG_FILE, $payment);

                $payment = Mage::helper('mercadopago')->setPayerInfo($payment);
                $core = Mage::getModel('mercadopago/core');
                Mage::helper('mercadopago')->log("Update Order", self::LOG_FILE);
                $core->updateOrder($payment);
                $core->setStatusOrder($payment, $stateObject);
            }

            return true;
        endif;

        return false;
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
        $info_form = $info_form['mercadopago_custom'];
        if (isset($info_form['one_click_pay']) && $info_form['one_click_pay'] == 1) {
            $info_form = $this->cleanFieldsOcp($info_form);
        }

        if (empty($info_form['token'])) {
            $exception = new MercadoPago_Core_Model_Api_V1_Exception();
            $exception->setMessage($exception->getUserMessage());
            throw $exception;
        }

        Mage::helper('mercadopago')->log("info form", self::LOG_FILE, $info_form);
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation($info_form);
        $info->setAdditionalInformation('payment_type_id', "credit_card");
        if (!empty($info_form['card_expiration_month']) && !empty($info_form['card_expiration_year'])) {
            $info->setAdditionalInformation('expiration_date', $info_form['card_expiration_month'] . "/" . $info_form['card_expiration_year']);
        }
        $info->setAdditionalInformation('payment_method', $info_form['payment_method_id']);
        $info->setAdditionalInformation('cardholderName', $info_form['card_holder_name']);

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
        $payment_info = [];

        if ($payment->getAdditionalInformation("coupon_code") != "") {
            $payment_info['coupon_code'] = $payment->getAdditionalInformation("coupon_code");
        }

        if ($payment->getAdditionalInformation("doc_number") != "") {
            $payment_info['identification_type'] = $payment->getAdditionalInformation("doc_type");
            $payment_info['identification_number'] = $payment->getAdditionalInformation("doc_number");
        }

        return $payment_info;
    }

    public function preparePostPayment()
    {
        Mage::helper('mercadopago')->log("Credit Card -> init prepare post payment", self::LOG_FILE);
        $core = Mage::getModel('mercadopago/core');
        $quote = $this->_getQuote();
        $order_id = $quote->getReservedOrderId();
        $order = $this->_getOrder($order_id);

        $payment = $order->getPayment();
        $payment_info = $this->getPaymentInfo($payment);

        $preference = $core->makeDefaultPreferencePaymentV1($payment_info);

        $preference['installments'] = (int)$payment->getAdditionalInformation("installments");
        $preference['payment_method_id'] = $payment->getAdditionalInformation("payment_method");
        $preference['token'] = $payment->getAdditionalInformation("token");

        if ($payment->getAdditionalInformation("issuer_id") != "") {
            $preference['issuer_id'] = (int)$payment->getAdditionalInformation("issuer_id");
        }

        if ($payment->getAdditionalInformation("customer_id") != "") {
            $preference['payer']['id'] = $payment->getAdditionalInformation("customer_id");
        }

        $preference['binary_mode'] = Mage::getStoreConfigFlag('payment/mercadopago_custom/binary_mode');
        $preference['statement_descriptor'] = Mage::getStoreConfig('payment/mercadopago_custom/statement_descriptor');

        Mage::helper('mercadopago')->log("Credit Card -> PREFERENCE to POST /v1/payments", self::LOG_FILE, $preference);

        /* POST /v1/payments */
        $response = $core->postPaymentV1($preference);

        $order->save();

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
        if (empty($email)){
            return false;
        }
        $access_token = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);

        $mp = Mage::helper('mercadopago')->getApiInstance($access_token);

        $customer = $mp->get("/v1/customers/search", ["email" => $email]);

        Mage::helper('mercadopago')->log("Response search customer", self::LOG_FILE, $customer);

        if ($customer['status'] == 200) {

            if ($customer['response']['paging']['total'] > 0) {
                return $customer['response']['results'][0];
            } else {
                Mage::helper('mercadopago')->log("Customer not found: " . $email, self::LOG_FILE);

                $customer = $mp->post("/v1/customers", ["email" => $email]);

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
        $params = ["token" => $token];
        if (isset($payment['issuer_id'])){
            $params['issuer_id'] = (int)$payment['issuer_id'];
        }
        if (isset($payment['payment_method_id'])){
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
        return Mage::getUrl('mercadopago/success', ['_secure' => true]);
    }

    public function getCode() {
        return $this->_code;
    }

}
