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
    const XML_PATH_USE_SUCCESSPAGE_MP = 'payment/mercadopago/use_successpage_mp';

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

        // if (empty($this->_apiInstance)) {
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

        $api->set_type(self::TYPE . ' ' . (string) Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version);

        $this->_apiInstance = $api;
        // }

        // set data sdk rest client
        MercadoPago_Lib_RestClient::setModuleVersion((string) Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version);
        MercadoPago_Lib_RestClient::setUrlStore(Mage::getStoreConfig('web/secure/base_url'));
        MercadoPago_Lib_RestClient::setEmailAdmin(Mage::getStoreConfig('trans_email/ident_general/email'));
        MercadoPago_Lib_RestClient::setCountryInitial(Mage::getStoreConfig('general/country/default'));

        return $this->_apiInstance;
    }

    public function isValidAccessToken($accessToken)
    {
        $mp = Mage::helper('mercadopago')->getApiInstance($accessToken);
        try {
            $response = $mp->get("/v1/payment_methods");
            if ($response['status'] == 401 || $response['status'] == 400) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isValidClientCredentials($clientId, $clientSecret)
    {
        $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
        try {
            $resultado = $mp->get_access_token();
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
        $couponAmount = $this->_getMultiCardValue($data, 'coupon_amount');
        $transactionAmount = $this->_getMultiCardValue($data, 'transaction_amount');

        if (isset($data['total_paid_amount'])) {
          $paidAmount = $this->_getMultiCardValue($data, 'total_paid_amount');
        } else {
          $paidAmount = $this->_getMultiCardValue($data, 'transaction_amount');
        }

        $shippingCost = $this->_getMultiCardValue($data, 'shipping_cost');

        if(isset($data['shipping_amount'])){
          $shippingCost = $this->_getMultiCardValue($data, 'shipping_amount');    
        }      

        $originalAmount = $transactionAmount + $shippingCost;


        if ($couponAmount && Mage::getStoreConfigFlag('payment/mercadopago/consider_discount')) {
            $order->setDiscountCouponAmount($couponAmount * -1);
            $order->setBaseDiscountCouponAmount($couponAmount * -1);
            $financingCost = $paidAmount + $couponAmount - $originalAmount;
        } else {
            //if a discount was applied and should not be considered
            $paidAmount += $couponAmount;
            $financingCost = $paidAmount - $originalAmount;
        }




        if ($shippingCost > 0) {
            $order->setBaseShippingAmount($shippingCost);
            $order->setShippingAmount($shippingCost);
        }


        if (!Mage::getStoreConfigFlag('payment/mercadopago/financing_cost')) {
            $order->setGrandTotal($paidAmount - $financingCost);
            $order->setBaseGrandTotal($paidAmount - $financingCost);

            return;
        } else {
            $order->setGrandTotal($paidAmount);
            $order->setBaseGrandTotal($paidAmount);
        }

        if (Zend_Locale_Math::round($financingCost, 4) > 0) {
            $order->setFinanceCostAmount($financingCost);
            $order->setBaseFinanceCostAmount($financingCost);
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
            $url = 'mercadopago/checkout/page';
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

    /**
     * Summary: Get client id from access token.
     * Description: Get client id from access token.
     *
     * @param String $at
     *
     * @return String client id.
     */
    public static function getClientIdFromAccessToken($at)
    {
        $t = explode('-', $at);
        if (count($t) > 1) {
            return $t[1];
        }

        return '';
    }

    public function getAnalyticsData($order = null)
    {
        $analyticsData = array();
        if ($order != null && $order->getId() && $order->getPayment()->getData('method')) {
            $additionalInfo = $order->getPayment()->getData('additional_information');
            $methodCode = $order->getPayment()->getData('method');
            $analyticsData = array(
                'payment_id'    => isset($additionalInfo['payment_id_detail']) ? $order->getPayment()->getData('additional_information')['payment_id_detail'] : '',
                'payment_type'  => 'credit_card',
                'checkout_type' => 'custom'
            );
            if ($methodCode == 'mercadopago_custom') {
                $analyticsData['public_key'] =  Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);
            } elseif ($methodCode == 'mercadopago_standard') {
                $analyticsData['analytics_key'] =  Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
                $analyticsData['checkout_type'] = 'basic';
                $analyticsData['payment_type'] = isset($additionalInfo['payment_type_id']) ? $order->getPayment()->getData('additional_information')['payment_type_id'] : 'credit_card';
            } else {
                $analyticsData['analytics_key'] = $this->getClientIdFromAccessToken(Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN));
                $analyticsData['payment_type'] = 'ticket';
            }
        } else {
            $analyticsData['platform_version'] = (string)Mage::getVersion();
            $analyticsData['module_version'] = (string)Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version;
            $analyticsData['email'] = Mage::getModel('mercadopago/core')->getEmailCustomer();
            $analyticsData['user_logged'] = Mage::getSingleton('customer/session')->getCustomer()->getId() !== 0 ? 1 : 0;
            $analyticsData['payment_methods'] = implode(',', array_keys(Mage::getSingleton('payment/config')->getActiveMethods()));

            $analyticsData['custom_analytics_key'] = Mage::getStoreConfig(self::XML_PATH_PUBLIC_KEY);
            $analyticsData['ticket_analytics_key'] = $this->getClientIdFromAccessToken(Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN));
            $analyticsData['standard_analytics_key'] = Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
        }

        return $analyticsData;
    }

    public function getPlatformInfo()
    {
        return array(
            "platform"         => "Magento",
            "platform_version" => (string)Mage::getVersion(),
            "module_version"   => (string)Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version,
            "code_version"     => phpversion()
        );
    }

    public function checkAnalyticsData()
    {
        $clientId = $this->_website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = $this->_website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        if (!empty($clientId) && !empty($clientSecret)) {
            $this->sendAnalyticsData(Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret));
        } else {
            $accessToken = $this->_website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
            if (!empty($accessToken)) {
                $this->sendAnalyticsData(Mage::helper('mercadopago')->getApiInstance($accessToken));
            }

        }

    }

    protected function sendAnalyticsData($api)
    {
        $request = array(
            "data" => $this->getPlatformInfo()
        );
        $fields = array(
            'two_cards'                          => $this->_website->getConfig('payment/mercadopago_custom/allow_2_cards'),
            'checkout_basic'                     => $this->_website->getConfig('payment/mercadopago_standard/active'),
            'checkout_custom_credit_card'        => $this->_website->getConfig('payment/mercadopago_custom/active'),
            'checkout_custom_ticket'             => $this->_website->getConfig('payment/mercadopago_customticket/active'),
            'mercado_envios'                     => $this->_website->getConfig('carriers/mercadoenvios/active'),
            'checkout_custom_credit_card_coupon' => $this->_website->getConfig('payment/mercadopago_custom/coupon_mercadopago'),
            'checkout_custom_ticket_coupon'      => $this->_website->getConfig('payment/mercadopago_customticket/coupon_mercadopago')
        );
        foreach ($fields as $key => $field) {
            $request['data'][$key] = $field == 1 ? 'true' : 'false';
        }

        $this->log("Analytics settings request sent /modules/tracking/settings", 'mercadopago_analytics.log', $request);
        $account_settings = $api->post("/modules/tracking/settings", $request['data']);
        $this->log("Analytics settings response", 'mercadopago_analytics.log', $account_settings);

    }

    public function getVersionModule(){
      return (string) Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version;
    }

}
