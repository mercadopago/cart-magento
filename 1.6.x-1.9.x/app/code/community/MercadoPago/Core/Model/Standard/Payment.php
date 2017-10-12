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
class MercadoPago_Core_Model_Standard_Payment
    extends Mage_Payment_Model_Method_Abstract
{
    protected $_formBlockType = 'mercadopago/standard_form';
    protected $_infoBlockType = 'mercadopago/standard_info';

    protected $_code = 'mercadopago_standard';

    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canReviewPayment = true;

    const LOG_FILE = 'mercadopago-standard.log';

    public function postPago()
    {
        //get mercadopago api instance
        $client_id = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $client_secret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        $mp = Mage::helper('mercadopago')->getApiInstance($client_id, $client_secret);

        //create preference
        $pref = $this->makePreference();
        Mage::helper('mercadopago')->log("make array", self::LOG_FILE, $pref);

        //make payment request
        $response = $mp->create_preference($pref);
        Mage::helper('mercadopago')->log("create preference result", self::LOG_FILE, $response);

        if ($response['status'] == 200 || $response['status'] == 201) {
            $payment = $response['response'];
            if (Mage::getStoreConfigFlag('payment/mercadopago_standard/sandbox_mode')) {
                $init_point = $payment['sandbox_init_point'];
            } else {
                $init_point = $payment['init_point'];
            }

            $arrayAssign = array(
                "init_point"      => $init_point,
                "type_checkout"   => $this->getConfigData('type_checkout'),
                "iframe_width"    => $this->getConfigData('iframe_width'),
                "iframe_height"   => $this->getConfigData('iframe_height'),
                "banner_checkout" => $this->getConfigData('banner_checkout'),
                "status"          => 201
            );

            Mage::helper('mercadopago')->log("Array preference ok", self::LOG_FILE);
        } else {
            $arrayAssign = array(
                "message" => Mage::helper('mercadopago')->__('An error has occurred. Please refresh the page.'),
                "json"    => json_encode($response),
                "status"  => 400
            );

            Mage::helper('mercadopago')->log("Array preference error", self::LOG_FILE);
        }

        return $arrayAssign;
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('mercadopago/pay', array('_secure' => true));
    }

    public function getDiscount($order)
    {
        $discount = 0;
        $order = $order->getData();

        if (isset($order['base_discount_amount']) && $order['base_discount_amount'] < 0) {
            $discount = $order['base_discount_amount'];
        }

        return $discount;
    }

    protected function getItems($order)
    {
        $items = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $image = (string)Mage::helper('catalog/image')->init($product, 'image');

            $items[] = array(
                "id"          => $item->getSku(),
                "title"       => $product->getName(),
                "description" => $product->getName(),
                "picture_url" => $image,
                "category_id" => Mage::getStoreConfig('payment/mercadopago/category_id'),
                "quantity"    => (int)number_format($item->getQtyOrdered(), 0, '.', ''),
                "unit_price"  => (float)number_format($item->getPrice(), 2, '.', '')
            );
        }

        return $items;
    }

    protected function getTotalItems($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }

        return $total;
    }

    protected function getExcludedPaymentsMethods()
    {
        $excludedMethods = array();
        $excludedPaymentMethods = $this->getConfigData('excluded_payment_methods');
        $arrEpm = explode(",", $excludedPaymentMethods);
        if (count($arrEpm) > 0) {
            foreach ($arrEpm as $m) {
                $excludedMethods[] = array("id" => $m);
            }
        }

        return $excludedMethods;
    }

    public function makePreference()
    {
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $payment = $order->getPayment();
        $paramsShipment = new Varien_Object();

        Mage::dispatchEvent('mercadopago_standard_make_preference_before',
            array('params' => $paramsShipment, 'order' => $order));

        $arr = array();
        $arr['external_reference'] = $orderIncrementId;
        $arr['items'] = $this->getItems($order);

        $this->_calculateDiscountAmount($arr['items'], $order);
        $this->_calculateBaseTaxAmount($arr['items'], $order);
        $totalItem = $this->getTotalItems($arr['items']);
        $totalItem += (float)$order->getBaseShippingAmount();
        $orderAmount = (float)$order->getBaseGrandTotal();
        if (!$orderAmount) {
            $orderAmount = (float)$order->getBasePrice() + $order->getBaseShippingAmount();
        }
        if ($totalItem > $orderAmount || $totalItem < $orderAmount) {
            $diffPrice = $orderAmount - $totalItem;
            $arr['items'][] = array(
                "title"       => "Difference amount of the items with a total",
                "description" => "Difference amount of the items with a total",
                "category_id" => Mage::getStoreConfig('payment/mercadopago/category_id'),
                "quantity"    => 1,
                "unit_price"  => (float)$diffPrice
            );
            Mage::helper('mercadopago')->log("Total itens: " . $totalItem, self::LOG_FILE);
            Mage::helper('mercadopago')->log("Total order: " . $orderAmount, self::LOG_FILE);
            Mage::helper('mercadopago')->log("Difference add itens: " . $diffPrice, self::LOG_FILE);
        }
        if ($order->canShip()) {
            $shippingAddress = $order->getShippingAddress();
            $shipping = $shippingAddress->getData();

            $arr['payer']['phone'] = array(
                "area_code" => "-",
                "number"    => $shipping['telephone']
            );

            $arr['shipments'] = $this->_getParamShipment($paramsShipment, $order, $shippingAddress);
        }
        $billingAddress = $order->getBillingAddress()->getData();

        $arr['payer']['date_created'] = date('Y-m-d', $customer->getCreatedAtTimestamp()) . "T" . date('H:i:s', $customer->getCreatedAtTimestamp());

        $this->_setPayerData($arr['payer'], $customer, $billingAddress);

        if (isset($payment['additional_information']['doc_number']) && $payment['additional_information']['doc_number'] != "") {
            $arr['payer']['identification'] = array(
                "type"   => "CPF",
                "number" => $payment['additional_information']['doc_number']
            );
        }

        $arr['payer']['address'] = array(
            "zip_code"      => $billingAddress['postcode'],
            "street_name"   => $billingAddress['street'] . " - " . $billingAddress['city'] . " - " . $billingAddress['country_id'],
            "street_number" => ""
        );

        $url = 'mercadopago/checkout/page';
        $arr['back_urls'] = array(
            "success" => Mage::getUrl($url),
            "pending" => Mage::getUrl($url),
            "failure" => Mage::getUrl('checkout/onepage/failure')
        );

        $arr['notification_url'] = Mage::getUrl('mercadopago/notifications/standard');

        $arr['payment_methods']['excluded_payment_methods'] = $this->getExcludedPaymentsMethods();
        $installments = $this->getConfigData('installments');
        if ((int)$installments > 0){
          $arr['payment_methods']['installments'] = (int)$installments;
        }

        $autoReturn = $this->getConfigData('auto_return');
        if ($autoReturn == 1) {
            $arr['auto_return'] = "approved";
        }

        $sponsorId = Mage::getStoreConfig('payment/mercadopago/sponsor_id');
        Mage::helper('mercadopago')->log("Sponsor_id", self::LOG_FILE, $sponsorId);
        if (!empty($sponsorId)) {
            Mage::helper('mercadopago')->log("Sponsor_id identificado", self::LOG_FILE, $sponsorId);
            $arr['sponsor_id'] = (int)$sponsorId;
        }

        return $arr;
    }

    protected function _setPayerData(&$arr, $customer, $billingAddress) {
        if (!$customer->getId()) {
            $arr['payer']['email'] = htmlentities($billingAddress['email']);
            $arr['payer']['first_name'] = htmlentities($billingAddress['firstname']);
            $arr['payer']['last_name'] = htmlentities($billingAddress['lastname']);
        } else {
            $arr['payer']['email'] = htmlentities($customer->getEmail());
            $arr['payer']['first_name'] = htmlentities($customer->getFirstname());
            $arr['payer']['last_name'] = htmlentities($customer->getLastname());
        }
    }

    protected function getReceiverAddress($shippingAddress)
    {
        return array(
            "floor"         => "-",
            "zip_code"      => $shippingAddress->getPostcode(),
            "street_name"   => $shippingAddress->getStreet()[0] . " - " . $shippingAddress->getCity() . " - " . $shippingAddress->getCountryId(),
            "apartment"     => "-",
            "street_number" => ""
        );
    }

    protected function _getParamShipment($params, $order, $shippingAddress) {
        $paramsShipment = $params->getValues();
        if (empty($paramsShipment)) {
            $paramsShipment = $params->getData();
            $paramsShipment['cost'] = (float)$order->getBaseShippingAmount();
        }
        $paramsShipment['receiver_address'] = $this->getReceiverAddress($shippingAddress);
        return $paramsShipment;
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $parent = parent::isAvailable($quote);
        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        $standard = (!empty($clientId) && !empty($clientSecret));

        if (!$parent || !$standard) {
            return false;
        }

        return Mage::helper('mercadopago')->isValidClientCredentials($clientId, $clientSecret);

    }

    protected function _calculateDiscountAmount(&$arr, $order)
    {
        if ($order->getDiscountAmount() < 0) {
            $arr[] = array(
                "title"       => "Store discount coupon",
                "description" => "Store discount coupon",
                "category_id" => Mage::getStoreConfig('payment/mercadopago/category_id'),
                "quantity"    => 1,
                "unit_price"  => (float)$order->getDiscountAmount()
            );
        }
    }

    protected function _calculateBaseTaxAmount(&$arr, $order)
    {
        if ($order->getBaseTaxAmount() > 0) {
            $arr[] = array(
                "title"       => "Store taxes",
                "description" => "Store taxes",
                "category_id" => Mage::getStoreConfig('payment/mercadopago/category_id'),
                "quantity"    => 1,
                "unit_price"  => (float)$order->getBaseTaxAmount()
            );
        }
    }

}
