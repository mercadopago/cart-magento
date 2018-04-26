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
    protected $_accessToken;
    protected $_clientId;
    protected $_clientSecret;

    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canReviewPayment = true;

    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';
    const LOG_FILE = 'mercadopago-custom.log';

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

    public function getInfoPaymentByOrder($orderId)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $payment = $order->getPayment();
        $infoPayments = array();
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
            array("field" => "activation_uri", "title" => "Generate Ticket"),
            array("field" => "payment_id_detail", "title" => "Mercado Pago Payment Id: %s")

        );

        foreach ($fields as $field) {
            if ($payment->getAdditionalInformation($field['field']) != "") {
                $text = Mage::helper('mercadopago')->__($field['title'], Mage::helper('mercadopago')->__($payment->getAdditionalInformation($field['field'])));
                $infoPayments[$field['field']] = array(
                    "text"  => $text,
                    "value" => Mage::helper('mercadopago')->__($payment->getAdditionalInformation($field['field']))
                );
            }
        }

        if ($payment->getAdditionalInformation('payer_identification_type') != "") {
            $text = __($payment->getAdditionalInformation('payer_identification_type'). ': '. $payment->getAdditionalInformation('payer_identification_number'));
            $infoPayments[$payment->getAdditionalInformation('payer_identification_type')] = array(
                "text"  => $text,
                "value" => $payment->getAdditionalInformation('payer_identification_number')
            );
        }

        return $infoPayments;
    }

    protected function validStatusTwoPayments($status)
    {
        $arrayStatus = explode(" | ", $status);
        $statusVerif = true;
        $statusFinal = "";
        foreach ($arrayStatus as $status):

            if ($statusFinal == "") {
                $statusFinal = $status;
            } else {
                if ($statusFinal != $status) {
                    $statusVerif = false;
                }
            }
        endforeach;

        if ($statusVerif === false) {
            $statusFinal = "other";
        }

        return $statusFinal;
    }

    public function getMessageByStatus($status, $statusDetail, $paymentMethod, $installment, $amount)
    {
      // @NEED_REFACTOR
      $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
      $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
      $payment = $order->getPayment();


      $status = $payment->getAdditionalInformation('status');
      $statusDetail = $payment->getAdditionalInformation('status_detail');
      $paymentMethod = $payment->getAdditionalInformation('payment_method');
      $installment = $payment->getAdditionalInformation('installments');
      $amount = $this->getTotalOrder($order);

      $status = $this->validStatusTwoPayments($status);
      $statusDetail = $this->validStatusTwoPayments($statusDetail);

      $message = array(
        "title"   => "",
        "message" => ""
      );

      $rawMessage = Mage::helper('mercadopago/statusMessage')->getMessage($status);
      $message['title'] = Mage::helper('mercadopago')->__($rawMessage['title']);

      if ($status == 'rejected') {
        if ($statusDetail == 'cc_rejected_invalid_installments') {
          $message['message'] = Mage::helper('mercadopago')
          ->__(Mage::helper('mercadopago/statusDetailMessage')->getMessage($statusDetail), strtoupper($paymentMethod), $installment);
        } elseif ($statusDetail == 'cc_rejected_call_for_authorize') {
          $message['message'] = Mage::helper('mercadopago')
          ->__(Mage::helper('mercadopago/statusDetailMessage')->getMessage($statusDetail), strtoupper($paymentMethod), $amount);
        } else {
          $message['message'] = Mage::helper('mercadopago')
          ->__(Mage::helper('mercadopago/statusDetailMessage')->getMessage($statusDetail), strtoupper($paymentMethod));
        }
      } else {
        $message['message'] = Mage::helper('mercadopago')->__($rawMessage['message']);
      }

      return $message;
    }

    protected function getCustomerInfo($customer, $order)
    {
        $email = htmlentities($customer->getEmail());
        if ($email == "") {
            $email = $order['customer_email'];
        }

        $firstName = htmlentities($customer->getFirstname());
        if ($firstName == "") {
            $firstName = $order->getBillingAddress()->getFirstname();
        }

        $lastName = htmlentities($customer->getLastname());
        if ($lastName == "") {
            $lastName = $order->getBillingAddress()->getLastname();
        }

        return array('email' => $email, 'first_name' => $firstName, 'last_name' => $lastName);
    }

    protected function getItemsInfo($order)
    {
        $dataItems = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $image = (string)Mage::helper('catalog/image')->init($product, 'image');

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

    protected function getCouponInfo($coupon, $couponCode)
    {
        $infoCoupon = array();
        $infoCoupon['coupon_amount'] = (float)$coupon['response']['coupon_amount'];
        $infoCoupon['coupon_code'] = $couponCode;
        $infoCoupon['campaign_id'] = $coupon['response']['id'];
        if ($coupon['status'] == 200) {
            Mage::helper('mercadopago')->log("Coupon applied. API response 200.", self::LOG_FILE);
        } else {
            Mage::helper('mercadopago')->log("Coupon invalid, not applied.", self::LOG_FILE);
        }

        return $infoCoupon;
    }

    public function makeDefaultPreferencePaymentV1($paymentInfo = array())
    {
        $quote = $this->_getQuote();
        $orderId = $quote->getReservedOrderId();
        $order = $this->_getOrder($orderId);
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $billingAddress = $quote->getBillingAddress()->getData();
        $customerInfo = $this->getCustomerInfo($customer, $order);

        /* INIT PREFERENCE */
        $preference = array();

        $preference['notification_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . "mercadopago/notifications/custom";

        $preference['description'] = Mage::helper('mercadopago')->__("Order # %s in store %s", $orderId, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true));
        if (isset($paymentInfo['transaction_amount'])) {
            $preference['transaction_amount'] = (float)$paymentInfo['transaction_amount'];
        } else {
            $preference['transaction_amount'] = (float)$this->getAmount();
        }

        $preference['external_reference'] = $orderId;
        $preference['payer']['email'] = $customerInfo['email'];

        if (!empty($paymentInfo['identification_type'])) {
            $preference['payer']['identification']['type'] = $paymentInfo['identification_type'];
            $preference['payer']['identification']['number'] = $paymentInfo['identification_number'];
        }
        $preference['additional_info']['items'] = $this->getItemsInfo($order);

        $preference['additional_info']['payer']['first_name'] = $customerInfo['first_name'];
        $preference['additional_info']['payer']['last_name'] = $customerInfo['last_name'];

        $preference['additional_info']['payer']['address'] = array(
            "zip_code"      => $billingAddress['postcode'],
            "street_name"   => $billingAddress['street'] . " - " . $billingAddress['city'] . " - " . $billingAddress['country_id'],
            "street_number" => ''
        );

        $preference['additional_info']['payer']['registration_date'] = date('Y-m-d', $customer->getCreatedAtTimestamp()) . "T" . date('H:i:s', $customer->getCreatedAtTimestamp());

        if ($order->canShip()) {
            $shippingAddress = $order->getShippingAddress();
            $shipping = $shippingAddress->getData();

            $preference['additional_info']['shipments']['receiver_address'] = array(
                "zip_code"      => $shipping['postcode'],
                "street_name"   => $shipping['street'] . " - " . $shipping['city'] . " - " . $shipping['country_id'],
                "street_number" => '',
                "floor"         => "-",
                "apartment"     => "-",

            );
        }

        $preference['additional_info']['payer']['phone'] = array(
            "area_code" => "0",
            "number"    => $billingAddress['telephone']
        );

        if (!empty($paymentInfo['coupon_code'])) {
            $couponCode = $paymentInfo['coupon_code'];
            Mage::helper('mercadopago')->log("Validating coupon_code: " . $couponCode, self::LOG_FILE);

            $coupon = $this->validCoupon($couponCode);
            Mage::helper('mercadopago')->log("Response API Coupon: ", self::LOG_FILE, $coupon);
            if(isset($coupon['status']) && $coupon['status'] < 300){
              $couponInfo = $this->getCouponInfo($coupon, $couponCode);
              $preference['coupon_amount'] = $couponInfo['coupon_amount'];
              $preference['coupon_code'] = strtoupper($couponInfo['coupon_code']);
              $preference['campaign_id'] = $couponInfo['campaign_id'];
              Mage::helper('mercadopago')->log("Applied coupon..", self::LOG_FILE);
            }

        }

        $sponsorId = Mage::getStoreConfig('payment/mercadopago/sponsor_id');
        Mage::helper('mercadopago')->log("Sponsor_id", 'mercadopago-standard.log', $sponsorId);
        if (!empty($sponsorId)) {
            Mage::helper('mercadopago')->log("Sponsor_id identificado", self::LOG_FILE, $sponsorId);
            $preference['sponsor_id'] = (int)$sponsorId;
        }

        return $preference;
    }


    public function postPaymentV1($preference)
    {

        if (!$this->_accessToken) {
            $this->_accessToken = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        }
        Mage::helper('mercadopago')->log("Access Token for Post", self::LOG_FILE, $this->_accessToken);

        //set sdk php mercadopago
        $mp = Mage::helper('mercadopago')->getApiInstance($this->_accessToken);
        $response = $mp->post("/v1/payments", $preference);
        Mage::helper('mercadopago')->log("POST /v1/payments", self::LOG_FILE, $response);

        if ($response['status'] == 200 || $response['status'] == 201) {
            return $response;
        } else {
            $e = "";
            $exception = new MercadoPago_Core_Model_Api_V1_Exception();
            if (count($response['response']['cause']) > 0) {
                foreach ($response['response']['cause'] as $error) {
                    $e .= $exception->getUserMessage($error) . " ";
                }
            } else {
                $e = $exception->getUserMessage();
            }

            Mage::helper('mercadopago')->log("error post pago: " . $e, self::LOG_FILE);
            Mage::helper('mercadopago')->log("response post pago: ", self::LOG_FILE, $response);

            $exception->setMessage($e);
            throw $exception;
        }
    }

    public function getPayment($payment_id)
    {
        if (!$this->_clientId || !$this->_clientSecret) {
            $this->_clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
            $this->_clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        }
        $mp = Mage::helper('mercadopago')->getApiInstance($this->_clientId, $this->_clientSecret);

        return $mp->get("/v1/payments/" . $payment_id);
    }

    public function getPaymentV1($payment_id)
    {
        if (!$this->_accessToken) {
            $this->_accessToken = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        }
        $mp = Mage::helper('mercadopago')->getApiInstance($this->_accessToken);

        return $mp->get("/v1/payments/" . $payment_id);
    }

    public function getMerchantOrder($merchant_order_id)
    {
        if (!$this->_clientId || !$this->_clientSecret) {
            $this->_clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
            $this->_clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        }
        $mp = Mage::helper('mercadopago')->getApiInstance($this->_clientId, $this->_clientSecret);

        return $mp->get("/merchant_orders/" . $merchant_order_id);
    }

    public function getPaymentMethods()
    {
        if (!$this->_accessToken) {
            $this->_accessToken = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        }

        $mp = Mage::helper('mercadopago')->getApiInstance($this->_accessToken);

        $payment_methods = $mp->get("/v1/payment_methods");

        return $payment_methods;
    }

    public function getEmailCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $email = $customer->getEmail();

        if (empty($email)) {
            $quote = $this->_getQuote();
            $email = $quote->getBillingAddress()->getEmail();
        }

        return $email;
    }


    public function getAmount()
    {
        $quote = $this->_getQuote();
        $total = $quote->getBaseSubtotalWithDiscount() + $quote->getShippingAddress()->getShippingAmount() + $quote->getShippingAddress()->getBaseTaxAmount();

        return (float) $total;
    }

    public function validCoupon($id)
    {
        if (!$this->_accessToken) {
            $this->_accessToken = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        }

        $mp = Mage::helper('mercadopago')->getApiInstance($this->_accessToken);

        $params = array(
            "transaction_amount" => $this->getAmount(),
            "payer_email"        => $this->getEmailCustomer(),
            "coupon_code"        => $id
        );

        $details_discount = $mp->get("/discount_campaigns", $params);

        //add value on return api discount
        $details_discount['response']['transaction_amount'] = $params['transaction_amount'];
        $details_discount['response']['params'] = $params;

        if($details_discount['status'] >= 400 && $details_discount['status'] < 500){
          $details_discount['response']['message'] = Mage::helper('mercadopago')->__($details_discount['response']['message']);
        }

        return $details_discount;
    }

    public function updateOrder($order = null, $data)
    {
        $helper = Mage::helper('mercadopago');
        $statusHelper = Mage::helper('mercadopago/statusUpdate');
        $helper->log('Update Order', 'mercadopago-notification.log');

        if (!isset($data['external_reference'])) {
            return;
        }

        if (!$order) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($data['external_reference']);
        }
      
        $paymentOrder = $order->getPayment();
        $this->_saveTransaction($data, $paymentOrder);

        if ($statusHelper->isStatusUpdated()) {
            return;
        }
        try {
            $additionalFields = array(
                'status',
                'status_detail',
                'payment_id',
                'transaction_amount',
                'cardholderName',
                'installments',
                'statement_descriptor',
                'trunc_card',
                'id',
                'payer_identification_type',
                'payer_identification_number'
            );

            $infoPayments = $paymentOrder->getAdditionalInformation();

            if (!isset($infoPayments['first_payment_id'])) {
                $paymentOrder = $this->_addAdditionalInformationToPaymentOrder($data, $additionalFields, $paymentOrder);
            }

            if (isset($data['id'])) {
                $paymentOrder->setAdditionalInformation('payment_id_detail', $data['id']);
            }

            if (isset($data['payer_identification_type']) & isset($data['payer_identification_number'])) {
                $paymentOrder->setAdditionalInformation($data['payer_identification_type'], $data['payer_identification_number']);
            }

            $paymentStatus = $paymentOrder->save();
            $helper->log('Update Payment', 'mercadopago.log', $paymentStatus->getData());

            $statusSave = $order->save();
            $helper->log('Update order', 'mercadopago.log', $statusSave->getData());
        } catch (Exception $e) {
            $helper->log('Error in update order status: ' . $e, 'mercadopago.log');
            $this->getResponse()->setBody($e);

            $this->getResponse()->setHttpResponseCode(MercadoPago_Core_Helper_Response::HTTP_BAD_REQUEST);
        }
    }

    protected function _addAdditionalInformationToPaymentOrder($data, $additionalFields, $paymentOrder){
        foreach ($additionalFields as $field) {
            if (isset($data[$field])) {
                $paymentOrder->setAdditionalInformation($field, $data[$field]);
            }
        }

        if (isset($data['payment_method_id'])) {
            $paymentOrder->setAdditionalInformation('payment_method', $data['payment_method_id']);
        }

        if (isset($data['merchant_order_id'])) {
            $paymentOrder->setAdditionalInformation('merchant_order_id', $data['merchant_order_id']);
        }
        return $paymentOrder;
    }

    protected function _saveTransaction($data, $paymentOrder)
    {
        try {
            $paymentOrder->setTransactionId($data['id']);
            $paymentOrder->setParentTransactionId($paymentOrder->getTransactionId());
            $transaction = $paymentOrder->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT, null, true, "");
            $transaction->setAdditionalInformation('raw_details_info', $data);
            $transaction->setIsClosed(true);
            $transaction->save();
        } catch (Exception $e) {
            Mage::helper('mercadopago')->log('error in update order status: ' . $e, 'mercadopago.log');
        }
    }

    public function getRecurringPayment($id)
    {
        if (!$this->_clientId || !$this->_clientSecret) {
            $this->_clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
            $this->_clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        }
        $mp = Mage::helper('mercadopago')->getApiInstance($this->_clientId, $this->_clientSecret);

        return $mp->get_preapproval_payment($id);
    }

    public function getTotalOrder($order){
      $total = $order->getBaseGrandTotal();

      if (!$total) {
          $total = $order->getBasePrice() + $order->getBaseShippingAmount();
      }

      $total = number_format($total, 2, '.', '');
      return $total;
    }

    // Identification Type

    public function getIdentificationType()
    {
        if (!$this->_accessToken) {
            $this->_accessToken = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        }

        $mp = Mage::helper('mercadopago')->getApiInstance($this->_accessToken);

        $payment_methods = $mp->get("/v1/identification_types");

        return $payment_methods;
    }


    public function getBanks()
    {
        if (!$this->_accessToken) {
            $this->_accessToken = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        }

        $mp = Mage::helper('mercadopago')->getApiInstance($this->_accessToken);

        $array = array(
          'payment_type_id' => 'bank_transfer',
          'marketplace' => 'NONE'
        );

        $payment_methods = $mp->get("/v1/payment_methods/search");

        return $payment_methods;
    }


}
