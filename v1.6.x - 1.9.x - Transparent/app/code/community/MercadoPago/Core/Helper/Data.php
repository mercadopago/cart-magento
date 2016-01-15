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

    public function log($message, $file = "mercadopago.log", $array = null)
    {
        //pega a configuração de log no admin, essa variavel vem como true por padrão
        $action_log = Mage::getStoreConfig('payment/mercadopago/logs');

        //caso tenha um array, transforma em json para melhor visualização
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        //set log
        Mage::log($message, null, $file, $action_log);
    }

    public function getApiInstance()
    {
        $params = func_num_args();
        if ($params > 2 || $params < 1) {
            Mage::throwException("Invalid arguments. Use CLIENT_ID and CLIENT SECRET, or ACCESS_TOKEN");
        }
        if ($params == 1) {
            $api = new MercadoPago_Lib_Api(func_get_arg(0));
            $api->set_platform('openplatform');
        } else {
            $api = new MercadoPago_Lib_Api(func_get_arg(0), func_get_arg(1));
            $api->set_platform('std');
        }
        if (Mage::getStoreConfigFlag('payment/mercadopago/sandbox_mode')) {
            $api->sandbox_mode(true);
        }

        $api->set_type('magento');
        $api->set_so((string) Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version);

        return $api;

    }

    public function isValidAccessToken($accessToken)
    {
        $mp = Mage::helper('mercadopago')->getApiInstance($accessToken);
        $response = $mp->get("/v1/payment_methods");
        if ($response['status'] == 401 || $response['status'] == 400) {
            return false;
        }

        return true;
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
        return $this->getApiInstance($clientId, $clientSecret)->get_access_token();
    }

}
