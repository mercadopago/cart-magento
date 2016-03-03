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
class MercadoPago_Core_Model_Source_PaymentMethods
    extends Mage_Payment_Model_Method_Abstract
{
    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';

    public function toOptionArray()
    {
        $methods = array();
        $helper = Mage::helper('mercadopago');

        //adiciona um valor vazio caso nao queria excluir nada
        $methods[] = array("value" => "", "label" => "");

        $accessToken = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        if (empty($accessToken) && !$helper->isValidClientCredentials($clientId, $clientSecret)) {
            return $methods;
        }

        //if accessToken is empty uses clientId and clientSecret to obtain it
        if (empty($accessToken)) {
            $accessToken = $helper->getAccessToken();
        }

        $helper->log("Get payment methods by country... ", 'mercadopago.log');
        $helper->log("API payment methods: " . "/v1/payment_methods?access_token=" . $accessToken, 'mercadopago.log');
        $response = MercadoPago_Lib_RestClient::get("/v1/payment_methods?access_token=" . $accessToken);

        $helper->log("API payment methods", 'mercadopago.log', $response);

        if (isset($response['error'])) {
            return $methods;
        }

        $response = $response['response'];

        foreach ($response as $m) {
            if ($m['id'] != 'account_money') {
                $methods[] = array(
                    'value' => $m['id'],
                    'label' => $helper->__($m['name'])
                );
            }
        }

        return $methods;
    }
}
