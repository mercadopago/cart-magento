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
class MercadoPago_Core_Helper_Data
    extends Mage_Payment_Helper_Data
{

    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';
    const XML_PATH_PUBLIC_KEY = 'payment/mercadopago_custom_checkout/public_key';
    const XML_PATH_CLIENT_ID = 'payment/mercadopago_standard/client_id';
    const XML_PATH_CLIENT_SECRET = 'payment/mercadopago_standard/client_secret';

    const PLATFORM_V1_WHITELABEL = 'v1-whitelabel';
    const PLATFORM_DESKTOP = 'Desktop';
    const TYPE = 'magento';

    //calculator
    const XML_PATH_CALCULATOR_AVAILABLE = 'payment/mercadopago/calculalator_available';
    const XML_PATH_CALCULATOR_PAGES = 'payment/mercadopago/show_in_pages';

    const STATUS_ACTIVE = 'active';
    const PAYMENT_TYPE_CREDIT_CARD = 'credit_card';


    protected $_apiInstance;

    protected $_website;

    public function log($message, $file = "mercadopago.log", $array = null)
    {
        $actionLog = Mage::getStoreConfig('payment/mercadopago/logs');

        if ($actionLog) {
            if (!is_null($array)) {
                $message .= " - " . json_encode($array);
            }

            Mage::log($message, null, $file, $actionLog);
        }
    }

    public function getApiInstance()
    {
        if (empty($this->_apiInstance)) {
            $params = func_num_args();
            if ($params > 2 || $params < 1) {
                Mage::throwException("Invalid arguments. Use CLIENT_ID and CLIENT SECRET, or ACCESS_TOKEN");
            }
            if ($params == 1) {
                $api = new MercadoPago_Lib_Api(func_get_arg(0));
                $api->set_platform(self::PLATFORM_V1_WHITELABEL);
            } else {
                $api = new MercadoPago_Lib_Api(func_get_arg(0), func_get_arg(1));
                $api->set_platform(self::PLATFORM_DESKTOP);
            }
            if (Mage::getStoreConfigFlag('payment/mercadopago_standard/sandbox_mode')) {
                $api->sandbox_mode(true);
            }

            $api->set_type(self::TYPE . ' ' . (string)Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version);

            $this->_apiInstance = $api;
        }

        return $this->_apiInstance;
    }

    public function isValidAccessToken($accessToken)
    {
        $mp = Mage::helper('mercadopago')->getApiInstance($accessToken);
        try{
            $response = $mp->get("/v1/payment_methods");
            if ($response['status'] == 401 || $response['status'] == 400) {
                return false;
            }
            return true;
        } catch (\Exception $e){
            return false;
        }
    }

    public function isValidClientCredentials($clientId, $clientSecret)
    {
        $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
        try {
            $mp->get_access_token();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function getAccessToken()
    {
        $clientId = Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(self::XML_PATH_CLIENT_SECRET);
        try {
            $accessToken = $this->getApiInstance($clientId, $clientSecret)->get_access_token();
        } catch (\Exception $e) {
            $accessToken = false;
        }

        return $accessToken;
    }

    public function setOrderSubtotals($data, $order)
    {
        if (isset($data['total_paid_amount'])) {
            $balance = $this->_getMultiCardValue($data, 'total_paid_amount');
        } else {
            $balance = $data['transaction_details']['total_paid_amount'];
        }
        $shippingCost = $this->_getMultiCardValue($data, 'shipping_cost');

        $order->setGrandTotal($balance);
        $order->setBaseGrandTotal($balance);
        if ($shippingCost > 0) {
            $order->setBaseShippingAmount($shippingCost);
            $order->setShippingAmount($shippingCost);
        }

        $couponAmount = $this->_getMultiCardValue($data, 'coupon_amount');
        $transactionAmount = $this->_getMultiCardValue($data, 'transaction_amount');

        if ($couponAmount) {
            $order->setDiscountCouponAmount($couponAmount * -1);
            $order->setBaseDiscountCouponAmount($couponAmount * -1);
            $balance = $balance - ($transactionAmount - $couponAmount + $shippingCost);
        } else {
            $balance = $balance - $transactionAmount - $shippingCost;
        }

        if (!Mage::getStoreConfigFlag('payment/mercadopago/financing_cost')) {
            $order->setGrandTotal($order->getGrandTotal() - $balance);
            $order->setBaseGrandTotal($order->getBaseGrandTotal() - $balance);

            return;
        }

        if (Zend_Locale_Math::round($balance, 4) > 0) {
            $order->setFinanceCostAmount($balance);
            $order->setBaseFinanceCostAmount($balance);
        }
    }

    /**
     * @param $payment
     *
     * @return mixed
     */
    public function setPayerInfo(&$payment)
    {
        $payment["trunc_card"] = (isset($payment['card']["last_four_digits"])) ? "xxxx xxxx xxxx " . $payment['card']["last_four_digits"] : '';
        $payment["cardholder_name"] = (isset($payment['card']["cardholder"]["name"])) ? $payment['card']["cardholder"]["name"] : '';
        $payment['payer_first_name'] = (isset($payment['payer']['first_name'])) ? $payment['payer']['first_name'] : '';
        $payment['payer_last_name'] = (isset($payment['payer']['last_name'])) ? $payment['payer']['last_name'] : '';
        $payment['payer_email'] = (isset($payment['payer']['email'])) ? $payment['payer']['email'] : '';

        return $payment;
    }

    protected function _getMultiCardValue($data, $field)
    {
        $finalValue = 0;
        if (!isset($data[$field])) {
            return $finalValue;
        }
        $amountValues = explode('|', $data[$field]);
        $statusValues = explode('|', $data['status']);
        foreach ($amountValues as $key => $value) {
            $value = (float)str_replace(' ', '', $value);
            if (str_replace(' ', '', $statusValues[$key]) == 'approved') {
                $finalValue = $finalValue + $value;
            }
        }

        return $finalValue;
    }

    public function getSuccessUrl()
    {
        if (Mage::getStoreConfig('payment/mercadopago/use_successpage_mp')) {
            $url = 'mercadopago/success';
        } else {
            $url = 'checkout/onepage/success';
        }

        return $url;
    }

    /**
     * Return the website associated to admin combo select
     *
     * @return Mage_Core_Model_Website
     */
    public function getAdminSelectedWebsite()
    {
        if (isset($this->_website)) {
            return $this->_website;
        }

        $websiteId = Mage::getSingleton('adminhtml/config_data')->getWebsite();

        if ($websiteId) {
            $this->_website = Mage::app()->getWebsite($websiteId);
        } else {
            $this->_website = Mage::app()->getWebsite();
        }

        return $this->_website;
    }

    /**
     *
     * @return boolean
     */
    public function isAvailableCalculator()
    {
        return Mage::getStoreConfig(self::XML_PATH_CALCULATOR_AVAILABLE);
    }

    /**
     *
     * @return mixed
     */
    public function getPagesToShow()
    {
        return Mage::getStoreConfig(self::XML_PATH_CALCULATOR_PAGES);
    }

    /**
     * return the list of payment methods or null
     *
     * @param mixed|null $accessToken
     *
     * @return mixed
     */
    public function getMercadoPagoPaymentMethods($accessToken)
    {
        $mp = Mage::helper('mercadopago')->getApiInstance($accessToken);
        $response = $mp->get("/v1/payment_methods");
        if ($response['status'] == 401 || $response['status'] == 400) {
            return false;
        }

        return $response['response'];
    }

}
