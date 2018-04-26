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
class MercadoPago_Core_Model_CustomTicket_Payment
    extends MercadoPago_Core_Model_CustomPayment
{
    protected $_formBlockType = 'mercadopago/customticket_form';
    protected $_infoBlockType = 'mercadopago/customticket_info';

    protected $_code = 'mercadopago_customticket';

    protected $fields_febraban = array(
      "firstname", "lastname", "doc-type","doc-number", "address", "address-number", "address-city", "address-state", "address-zipcode"
    );
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
        Mage::helper('mercadopago')->log("Ticket -> initialize", 'mercadopago-custom.log');

        $response = $this->preparePostPayment();

        if ($response !== false) {
            $this->getInfoInstance()->setAdditionalInformation('activation_uri', $response['response']['transaction_details']['external_resource_url']);
            $this->getInfoInstance()->setAdditionalInformation('payment_id_detail', $response['response']['id']);
            return true;
        }

        return false;
    }

    public function assignData($data)
    {
        // route /checkout/onepage/savePayment
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $infoForm = $data->getData();
        $infoForm = $infoForm['mercadopago_customticket'];

        Mage::helper('mercadopago')->log("Ticket -> info form", 'mercadopago-custom.log', $infoForm);

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('payment_method', $infoForm['payment_method_ticket']);

        if (isset($infoForm['coupon_code'])) {
            $info->setAdditionalInformation('coupon_code', $infoForm['coupon_code']);
        }

        // Fields for new febraban rule
        foreach ($this->fields_febraban as $key) {
          if (isset($infoForm[$key])) {
            $info->setAdditionalInformation($key, $infoForm[$key]);
          }
        }

        return $this;
    }

    public function preparePostPayment()
    {
      
        //check actual time
        $init = microtime(true);
      
        Mage::helper('mercadopago')->log("Ticket -> init prepare post payment", 'mercadopago-custom.log');
        $core = Mage::getModel('mercadopago/core');
        $quote = $this->_getQuote();
        $orderId = $quote->getReservedOrderId();
        $order = $this->_getOrder($orderId);

        $payment = $order->getPayment();

        $paymentInfo = array();

        if ($payment->getAdditionalInformation("coupon_code") != "") {
            $paymentInfo['coupon_code'] = $payment->getAdditionalInformation("coupon_code");
        }

        $preference = $core->makeDefaultPreferencePaymentV1($paymentInfo);

        $preference['payment_method_id'] = $payment->getAdditionalInformation("payment_method");

        // febraban rule
        $date_of_expiration = Mage::getStoreConfig('payment/mercadopago_customticket/date_of_expiration');
        if($date_of_expiration >= 1 && $date_of_expiration <= 29){
          $preference['date_of_expiration'] = date('Y-m-d', strtotime("+" . $date_of_expiration . " days")) . "T00:00:01.000-03:00";
        }

        if ($payment->getAdditionalInformation("firstname") != "") {
          $preference['payer']['first_name'] = $payment->getAdditionalInformation("firstname");
        }

        if ($payment->getAdditionalInformation("lastname") != "") {
          $preference['payer']['last_name'] = $payment->getAdditionalInformation("lastname");
        }

        if ($payment->getAdditionalInformation("doc-type") != "") {
          $preference['payer']['identification']['type'] = $payment->getAdditionalInformation("doc-type");

          //remove last-name pessoa juridica
          if($preference['payer']['identification']['type'] == "CNPJ"){
            $preference['payer']['last_name'] = "";
          }
        }

        if ($payment->getAdditionalInformation("doc-number") != "") {
          $preference['payer']['identification']['number'] = $payment->getAdditionalInformation("doc-number");
        }

        if ($payment->getAdditionalInformation("address-zipcode") != "") {
          $preference['payer']['address']['zip_code'] = $payment->getAdditionalInformation("address-zipcode");
        }

        if ($payment->getAdditionalInformation("address") != "") {
          $preference['payer']['address']['street_name'] = $payment->getAdditionalInformation("address");
        }

        if ($payment->getAdditionalInformation("address-number") != "") {
          $preference['payer']['address']['street_number'] = $payment->getAdditionalInformation("address-number");
        }

        if ($payment->getAdditionalInformation("address-city") != "") {
          $preference['payer']['address']['city'] = $payment->getAdditionalInformation("address-city");
          $preference['payer']['address']['neighborhood'] = $payment->getAdditionalInformation("address-city");
        }

        if ($payment->getAdditionalInformation("address-state") != "") {
          $preference['payer']['address']['federal_unit'] = $payment->getAdditionalInformation("address-state");
        }

        Mage::helper('mercadopago')->log("Ticket -> PREFERENCE to POST /v1/payments", 'mercadopago-custom.log', $preference);

        /* POST /v1/payments */
        $response = $core->postPaymentV1($preference);
      
        //calculate time consumed
        $timeConsumed = round(microtime(true) - $init, 3); 
        Mage::helper('mercadopago')->log("Time consumed to create payment (ticket): " . $timeConsumed . "s", 'mercadopago-custom.log');
      
        return $response;
    }

    public function getOrderPlaceRedirectUrl()
    {

        Mage::helper('mercadopago')->log("Ticket -> getOrderPlaceRedirectUrl", 'mercadopago-custom.log');

        return Mage::getUrl('mercadopago/checkout/page', array('_secure' => true));
    }

}
