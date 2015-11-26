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
class MercadoPago_Core_Model_Core
    extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'mercadopago';

    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canReviewPayment = true;

    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';
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

    public function getInfoPaymentByOrder($order_id)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $payment = $order->getPayment();
        $info_payments = array();
        $fields = array(
            array("field" => "cardholderName", "title" => "Card Holder Name: %s"),
            array("field" => "trunc_card", "title" => "Card Number: %s"),
            array("field" => "payment_method", "title" => "Payment Method: %s"),
            array("field" => "expiration_date", "title" => "Expiration Date: %s"),
            array("field" => "installments", "title" => "Installments: %s"),
            array("field" => "statement_descriptor", "title" => "Statement Descriptor: %s"),
            array("field" => "payment_id", "title" => "Payment id (MercadoPago): %s"),
            array("field" => "status", "title" => "Payment Status: %s"),
            array("field" => "status_detail", "title" => "Payment Detail: %s"),
            array("field" => "activation_uri", "title" => "Generate Ticket")
        );

        foreach ($fields as $field):
            if ($payment->getAdditionalInformation($field['field']) != ""):
                $text = Mage::helper('mercadopago')->__($field['title'], $payment->getAdditionalInformation($field['field']));
                $info_payments[$field['field']] = array(
                    "text"  => $text,
                    "value" => $payment->getAdditionalInformation($field['field'])
                );
            endif;
        endforeach;

        return $info_payments;
    }

    protected function validStatusTwoPayments($status)
    {
        $array_status = explode(" | ", $status);
        $status_verif = true;
        $status_final = "";
        foreach ($array_status as $status):

            if ($status_final == "") {
                $status_final = $status;
            } else {
                if ($status_final != $status) {
                    $status_verif = false;
                }
            }
        endforeach;

        if ($status_verif === false) {
            $status_final = "other";
        }

        return $status_final;
    }

    public function getMessageByStatus($status, $status_detail, $payment_method, $installment, $amount)
    {
        $status = $this->validStatusTwoPayments($status);
        $status_detail = $this->validStatusTwoPayments($status_detail);

        $message = array(
            "title"   => "",
            "message" => ""
        );

        $rawMessage = Mage::helper('mercadopago/statusMessage')->getMessage($status);
        $message['title'] = Mage::helper('mercadopago')->__($rawMessage['title']);

        if ($status == 'rejected') {
            if ($status_detail == 'cc_rejected_invalid_installments') {
                $message['message'] = Mage::helper('mercadopago')
                    ->__(Mage::helper('mercadopago/statusDetailMessage')->getMessage($status_detail), strtoupper($payment_method), $installment);
            } elseif ($status_detail == 'cc_rejected_call_for_authorize') {
                $message['message'] = Mage::helper('mercadopago')
                    ->__(Mage::helper('mercadopago/statusDetailMessage')->getMessage($status_detail), strtoupper($payment_method), $amount);
            } else {
                $message['message'] = Mage::helper('mercadopago')
                    ->__(Mage::helper('mercadopago/statusDetailMessage')->getMessage($status_detail), strtoupper($payment_method));
            }
        } else {
            $message['message'] = Mage::helper('mercadopago')->__($rawMessage['message']);
        }

        return $message;
    }

    protected function getTotalCart($order)
    {
        $total_cart = $order->getBaseGrandTotal() - $order->getBaseFinanceCostAmount();
        if (!$total_cart) {
            $total_cart = $order->getBasePrice() + $order->getBaseShippingAmount() - $order->getBaseFinanceCostAmount();
        }

        return number_format($total_cart, 2, '.', '');
    }

    protected function getCustomerInfo($customer, $order)
    {
        $email = htmlentities($customer->getEmail());
        if ($email == "") {
            $email = $order['customer_email'];
        }

        $first_name = htmlentities($customer->getFirstname());
        if ($first_name == "") {
            $first_name = $order->getBillingAddress()->getFirstname();
        }

        $last_name = htmlentities($customer->getLastname());
        if ($last_name == "") {
            $last_name = $order->getBillingAddress()->getLastname();
        }

        return array('email' => $email, 'first_name' => $first_name, 'last_name' => $last_name);
    }

    protected function getItemsInfo($order)
    {
        $dataItems = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $image = (string) Mage::helper('catalog/image')->init($product, 'image');

            $dataItems[] = array(
                "id"          => $item->getSku(),
                "title"       => $product->getName(),
                "description" => $product->getName(),
                "picture_url" => $image,
                "category_id" => Mage::getStoreConfig('payment/mercadopago/category_id'),
                "quantity"    => (int)number_format($item->getQtyOrdered(), 0, '.', ''),
                "unit_price"  => (float)number_format($product->getPrice(), 2, '.', '')
            );
        }

        /* verify discount and add it like an item */
        $discount = $this->getDiscount();
        if ($discount != 0) {
            $dataItems[] = array(
                "title"       => "Discount by the Store",
                "description" => "Discount by the Store",
                "quantity"    => 1,
                "unit_price"  => (float)number_format($discount, 2, '.', '')
            );
        }

        return $dataItems;

    }

    protected function getCouponInfo($coupon, $coupon_code)
    {
        $infoCoupon = array();
        $infoCoupon['coupon_amount'] = (float)$coupon['response']['coupon_amount'];
        $infoCoupon['coupon_code'] = $coupon_code;
        if ($coupon['status'] == 200) {
            Mage::helper('mercadopago')->log("Coupon applied. API response 200.", 'mercadopago-custom.log');
        } else {
            Mage::helper('mercadopago')->log("Coupon invalid, not applied.", 'mercadopago-custom.log');
        }

        return $infoCoupon;
    }

    public function makeDefaultPreferencePaymentV1($payment_info = array())
    {
        $quote = $this->_getQuote();
        $order_id = $quote->getReservedOrderId();
        $order = $this->_getOrder($order_id);
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $billing_address = $quote->getBillingAddress()->getData();
        $customerInfo = $this->getCustomerInfo($customer, $order);

        /* INIT PREFERENCE */
        $preference = array();

        $preference['notification_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . "mercadopago/notifications/custom";
        $preference['transaction_amount'] = (float)$this->getTotalCart($order);
        $preference['external_reference'] = $order_id;
        $preference['payer']['email'] = $customerInfo['email'];

        if (!empty($payment_info['identification_type'])) {
            $preference['payer']['identification']['type'] = $payment_info['identification_type'];
            $preference['payer']['identification']['number'] = $payment_info['identification_number'];
        }
        $preference['additional_info']['items'] = $this->getItemsInfo($order);

        $preference['additional_info']['payer']['first_name'] = $customerInfo['first_name'];
        $preference['additional_info']['payer']['last_name'] = $customerInfo['last_name'];

        $preference['additional_info']['payer']['address'] = array(
            "zip_code"      => $billing_address['postcode'],
            "street_name"   => $billing_address['street'] . " - " . $billing_address['city'] . " - " . $billing_address['country_id'],
            "street_number" => ''
        );

        $preference['additional_info']['payer']['registration_date'] = date('Y-m-d', $customer->getCreatedAtTimestamp()) . "T" . date('H:i:s', $customer->getCreatedAtTimestamp());

        $shipping = $order->getShippingAddress()->getData();

        $preference['additional_info']['shipments']['receiver_address'] = array(
            "zip_code"      => $shipping['postcode'],
            "street_name"   => $shipping['street'] . " - " . $shipping['city'] . " - " . $shipping['country_id'],
            "street_number" => '',
            "floor"         => "-",
            "apartment"     => "-",

        );

        $preference['additional_info']['payer']['phone'] = array(
            "area_code" => "0",
            "number"    => $shipping['telephone']
        );

        if (!empty($payment_info['coupon_code'])) {
            $coupon_code = $payment_info['coupon_code'];
            Mage::helper('mercadopago')->log("Validating coupon_code: " . $coupon_code, 'mercadopago-custom.log');

            $coupon = $this->validCoupon($coupon_code);
            Mage::helper('mercadopago')->log("Response API Coupon: ", 'mercadopago-custom.log', $coupon);

            $couponInfo = $this->getCouponInfo($coupon,$coupon_code);
            $preference['coupon_amount'] = $couponInfo['coupon_amount'];
            $preference['coupon_code'] = $couponInfo['coupon_code'];

        }

        $sponsor_id = Mage::getStoreConfig('payment/mercadopago/sponsor_id');
        Mage::helper('mercadopago')->log("Sponsor_id", 'mercadopago-standard.log', $sponsor_id);
        if (!empty($sponsor_id)) {
            Mage::helper('mercadopago')->log("Sponsor_id identificado", 'mercadopago-custom.log', $sponsor_id);
            $preference['sponsor_id'] = (int)$sponsor_id;
        }

        return $preference;
    }


    public function postPaymentV1($preference)
    {

        //obtem access_token
        $access_token = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        Mage::helper('mercadopago')->log("Access Token for Post", 'mercadopago-custom.log', $access_token);

        //seta sdk php mercadopago
        $mp = Mage::helper('mercadopago')->getApiInstance($access_token);

        $response = $mp->post("/v1/payments", $preference);
        Mage::helper('mercadopago')->log("POST /v1/payments", 'mercadopago-custom.log', $response);

        if ($response['status'] == 200 || $response['status'] == 201) {
            return $response;
        } else {
            $e = "";
            $exception = new MercadoPago_Core_Model_Api_V1_Exception();
            foreach ($response['response']['cause'] as $error) {
                $e .= $exception->getUserMessage($error) . " ";
            }

            Mage::helper('mercadopago')->log("erro post pago: " . $e, 'mercadopago-custom.log');
            Mage::helper('mercadopago')->log("response post pago: ", 'mercadopago-custom.log', $response);

            $exception->setMessage($e);
            throw $exception;
        }
    }

    public function getPayment($payment_id)
    {
        $clienId = MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID;
        $clientSecret = MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET;
        $mp = Mage::helper('mercadopago')->getApiInstance($clienId,$clientSecret);

        return $mp->get_payment($payment_id);
    }

    public function getPaymentV1($payment_id)
    {
        $this->access_token = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        $mp = Mage::helper('mercadopago')->getApiInstance($this->access_token);

        return $mp->get("/v1/payments/" . $payment_id);
    }

    public function getMerchantOrder($merchant_order_id)
    {
        $clientId = MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID;
        $clientSecret = MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET;
        $mp = Mage::helper('mercadopago')->getApiInstance($clientId,$clientSecret);

        return $mp->get("/merchant_orders/" . $merchant_order_id);
    }

    public function getPaymentMethods()
    {
        $this->access_token = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);

        $mp = Mage::helper('mercadopago')->getApiInstance($this->access_token);

        $payment_methods = $mp->get("/v1/payment_methods");

        return $payment_methods;
    }

    public function getEmailCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $email = $customer->getEmail();

        if ($email == "") {
            $order = $this->_getOrder();
            $email = $order['customer_email'];
        }

        return $email;
    }


    public function getAmount()
    {
        $quote = $this->_getQuote();
        $total = $quote->getBaseGrandTotal();

        //caso o valor seja null setta um valor 0
        if (is_null($total)) {
            $total = 0;
        }

        return (float)$total;
    }

    public function validCoupon($id)
    {
        $this->access_token = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);

        $mp = Mage::helper('mercadopago')->getApiInstance($this->access_token);

        $params = array(
            "transaction_amount" => $this->getAmount(),
            "payer_email"        => $this->getEmailCustomer(),
            "coupon_code"        => $id
        );

        $details_discount = $mp->get("/discount_campaigns", $params);

        //add value on return api discount
        $details_discount['response']['transaction_amount'] = $params['transaction_amount'];
        $details_discount['response']['params'] = $params;


        return $details_discount;
    }
}
