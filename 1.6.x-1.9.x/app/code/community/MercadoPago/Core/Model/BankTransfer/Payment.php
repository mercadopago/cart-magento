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
class MercadoPago_Core_Model_BankTransfer_Payment
    extends MercadoPago_Core_Model_CustomPayment
{
    protected $_formBlockType = 'mercadopago/banktransfer_form';
    protected $_infoBlockType = 'mercadopago/banktransfer_info';

    protected $_code = 'mercadopago_banktransfer';

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
       Mage::helper('mercadopago')->log("Bank Transfer -> initialize", 'mercadopago-custom.log', $infoForm);
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
        $infoForm = $infoForm['mercadopago_banktransfer'];

        Mage::helper('mercadopago')->log("Bank Transfer -> info form", 'mercadopago-custom.log', $infoForm);

        $info = $this->getInfoInstance();

        $info->setAdditionalInformation('payment_method', 'pse');
        $info->setAdditionalInformation('identification_type', $infoForm['identification_type']);
        $info->setAdditionalInformation('identification_number', $infoForm['identification_number']);
        $info->setAdditionalInformation('financial_institutions', $infoForm['financial_institutions']);
        $info->setAdditionalInformation('legal_status', $infoForm['legal_status']);

        return $this;
    }

    public function preparePostPayment()
    {
        //check actual time
        $init = microtime(true);
        Mage::helper('mercadopago')->log("Bank Transfer -> init prepare post payment", 'mercadopago-custom.log');

        $core = Mage::getModel('mercadopago/core');
        $quote = $this->_getQuote();
        $orderId = $quote->getReservedOrderId();
        $order = $this->_getOrder($orderId);

        $payment = $order->getPayment();

        $paymentInfo = array();

        $preference = $core->makeDefaultPreferencePaymentV1($paymentInfo);

        $preference['payment_method_id'] = $payment->getAdditionalInformation("payment_method");
        $preference['payer']['identification']['type'] = $payment->getAdditionalInformation("identification_type");
        $preference['payer']['identification']['number'] = $payment->getAdditionalInformation("identification_number");
        $preference['payer']['entity_type'] = $payment->getAdditionalInformation("legal_status");
        $preference['transaction_details']['financial_institution'] = $payment->getAdditionalInformation("financial_institutions");
        $preference['callback_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);


        $ip = "";
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
          $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
          $ip = $_SERVER['REMOTE_ADDR'];
        }

        $preference['additional_info']['ip_address'] = $ip;

        Mage::helper('mercadopago')->log("Bank Transfer -> PREFERENCE to POST /v1/payments", 'mercadopago-custom.log', $preference);

         /* POST /v1/payments */
        $response = $core->postPaymentV1($preference);
      
        //calculate time consumed
        $timeConsumed = round(microtime(true) - $init, 3); 
        Mage::helper('mercadopago')->log("Time consumed to create payment (bank transfer): " . $timeConsumed . "s", 'mercadopago-custom.log');
      
        return $response;
    }

    public function getOrderPlaceRedirectUrl()
    {
        Mage::helper('mercadopago')->log("Bank Transfer -> getOrderPlaceRedirectUrl", 'mercadopago-custom.log');

        return Mage::getUrl('mercadopago/checkout/page', array('_secure' => true));
    }

}
