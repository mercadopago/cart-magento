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
class MercadoPago_Core_Model_Custom_Payment
    extends Mage_Payment_Model_Method_Abstract
{
    //configura o block do formulario e de informações sobre o pagamento
    protected $_formBlockType = 'mercadopago/custom_form';
    protected $_infoBlockType = 'mercadopago/custom_info';

    protected $_code = 'mercadopago_custom';

    protected $_canSaveCc = false;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canVoid = true;
    protected $_canCancelInvoice = true;
    protected $_isInitializeNeeded = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canReviewPayment = true;

    protected static $_accessTokenConfigPath = 'payment/mercadopago_custom_checkout/access_token';

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

        //verifica se o pagamento não é boleto, caso seja não tem card_token_id
        if ($this->getInfoInstance()->getAdditionalInformation('token') == "") {
            Mage::throwException(Mage::helper('mercadopago')->__('Verify the form data or wait until the validation of the payment data'));
        }


        //continua o processo de pagamento
        $response = $this->preparePostPayment();

        if ($response !== false):

            $payment = $response['response'];

            //set status
            $this->getInfoInstance()->setAdditionalInformation('status', $payment['status']);
            $this->getInfoInstance()->setAdditionalInformation('status_detail', $payment['status_detail']);

            return true;
        endif;

        return false;
    }

    public function assignData($data)
    {

        // route /checkout/onepage/savePayment
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        //get array info
        $info_form = $data->getData();
        $info_form = $info_form['mercadopago_custom'];

        Mage::helper('mercadopago')->log("info form", 'mercadopago-custom.log', $info_form);

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('payment_type_id', "credit_card");
        $info->setAdditionalInformation('token', $info_form['token']);
        $info->setAdditionalInformation('payment_method', $info_form['payment_method_id']);
        $info->setAdditionalInformation('installments', $info_form['installments']);
        $info->setAdditionalInformation('doc_type', $info_form['doc_type']);
        $info->setAdditionalInformation('doc_number', $info_form['doc_number']);

        //caso tenha banco, adiciona nas informações adicionais
        if (isset($info_form['issuer_id'])) {
            $info->setAdditionalInformation('issuer_id', $info_form['issuer_id']);
        }

        if (isset($info_form['coupon_code'])) {
            $info->setAdditionalInformation('coupon_code', $info_form['coupon_code']);
        }

        if (isset($info_form['customer_id'])) {
            $info->setAdditionalInformation('customer_id', $info_form['customer_id']);
        }

        if ($info_form['token'] != ""):
            if ($info_form['card_expiration_month'] != "-1" && $info_form['card_expiration_year'] != "-1") {
                $info->setAdditionalInformation('expiration_date', $info_form['card_expiration_month'] . "/" . $info_form['card_expiration_year']);
            }
            $info->setAdditionalInformation('cardholderName', $info_form['card_holder_name']);
        endif;

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

    protected function getPaymentInfo($payment) {
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

    public function preparePostPayment()
    {
        Mage::helper('mercadopago')->log("Credit Card -> init prepare post payment", 'mercadopago-custom.log');
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

        $preference['binary_mode'] = Mage::getStoreConfig('payment/mercadopago_custom/binary_mode') == 1 ? true : false;
        $preference['statement_descriptor'] = Mage::getStoreConfig('payment/mercadopago_custom/statement_descriptor');

        Mage::helper('mercadopago')->log("Credit Card -> PREFERENCE to POST /v1/payments", 'mercadopago-custom.log', $preference);

        /* POST /v1/payments */
        $response = $core->postPaymentV1($preference);

        if ($response !== false && $response['response']['status'] == 'approved') {
            $this->CustomerAndCards($preference['token'], $response['response']);
        }

        return $response;
    }

    public function CustomerAndCards($token, $payment_created)
    {
        $customer = $this->getOrCreateCustomer($payment_created['payer']['email']);

        if ($customer !== false) {
            $this->checkAndcreateCard($customer, $token, $payment_created);
        }
    }

    public function getOrCreateCustomer($email)
    {

        //obtem access_token
        $access_token = Mage::getStoreConfig(self::$_accessTokenConfigPath);

        //seta sdk php mercadopago
        $mp = Mage::helper('mercadopago')->getApiInstance($access_token);

        $customer = $mp->get("/v1/customers/search", array(
                "email" => $email
            )
        );

        Mage::helper('mercadopago')->log("Response search customer", 'mercadopago-custom.log', $customer);

        if ($customer['status'] == 200) {

            //verifica se foi encontrado algum customer
            if ($customer['response']['paging']['total'] > 0) {
                //retorna o customer encontrado
                return $customer['response']['results'][0];
            } else {
                Mage::helper('mercadopago')->log("Customer not found: " . $email, 'mercadopago-custom.log');

                //caso não exista, cria o customer
                $customer = $mp->post("/v1/customers", array(
                        "email" => $email
                    )
                );

                Mage::helper('mercadopago')->log("Response create customer", 'mercadopago-custom.log', $customer);

                //caso resposta 200 retorna o response
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
        //obtem access_token
        $access_token = Mage::getStoreConfig(self::$_accessTokenConfigPath);

        //seta sdk php mercadopago
        $mp = Mage::helper('mercadopago')->getApiInstance($access_token);

        foreach ($customer['cards'] as $card) {


            // verifica se o cartão ja existe a partir das info do payment e da api de customer
            if ($card['first_six_digits'] == $payment['card']['first_six_digits']
                && $card['last_four_digits'] == $payment['card']['last_four_digits']
                && $card['expiration_month'] == $payment['card']['expiration_month']
                && $card['expiration_year'] == $payment['card']['expiration_year']
            ) {
                Mage::helper('mercadopago')->log("Card already exists", 'mercadopago-custom.log', $card);

                // return - cartão ja existe
                return $card;
            }
        }

        // caso chegue aqui, o cartão não existe
        // faz o post na api para cadastrar
        $card = $mp->post("/v1/customers/" . $customer['id'] . "/cards", array(
                "token" => $token
            )
        );

        Mage::helper('mercadopago')->log("Response create card", 'mercadopago-custom.log', $card);

        //card created
        if ($card['status'] == 201) {
            return $card['response'];
        }

        return false;
    }

    public function getCustomerAndCards()
    {
        /* não reaproveito o request getOrCreateCustomer(), pois é 2 api calls */
        $email = Mage::getModel('mercadopago/core')->getEmailCustomer();

        $customer = $this->getOrCreateCustomer($email);

        return $customer;
    }


    public function getOrderPlaceRedirectUrl()
    {
        // requisicao vem da pagina de finalizacao de pedido
        return Mage::getUrl('mercadopago/success', array('_secure' => true));
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get admin checkout session namespace
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getAdminCheckout()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Retrieves Quote
     *
     * @param integer $quoteId
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId = null)
    {
        if (!empty($quoteId)) {
            return Mage::getModel('sales/quote')->load($quoteId);
        } else {
            if (Mage::app()->getStore()->isAdmin()) {
                return $this->_getAdminCheckout()->getQuote();
            } else {
                return $this->_getCheckout()->getQuote();
            }
        }
    }

    /**
     * Retrieves Order
     *
     * @param integer $incrementId
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder($incrementId)
    {
        return Mage::getModel('sales/order')->loadByIncrementId($incrementId);
    }
}
