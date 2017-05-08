<?php

/**
 *
 */
class MercadoPago_Core_Helper_Validate
{
    public function validateAccessToken($website)
    {
        $accessToken = $website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
        if (!empty($accessToken)) {
            if (!Mage::helper('mercadopago')->isValidAccessToken($accessToken)) {
                Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Custom Checkout: Invalid access token'));
            }
        }
    }

    public function validateClientCredentials($website)
    {
        $clientId = $website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = $website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        if (!empty($clientId) && !empty($clientSecret)) {
            if (!Mage::helper('mercadopago')->isValidClientCredentials($clientId, $clientSecret)) {
                Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Classic Checkout: Invalid client id or client secret'));
            }
        }
    }

    public function validateRecurringClientCredentials($website)
    {
        $clientId = $website->getConfig('payment/mercadopago_recurring/client_id');
        $clientSecret = $website->getConfig('payment/mercadopago_recurring/client_secret');
        if (!empty($clientId) && !empty($clientSecret)) {
            if (!Mage::helper('mercadopago')->isValidClientCredentials($clientId, $clientSecret)) {
                Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Recurring Payment Checkout: Invalid client id or client secret'));
            }
        }
    }

}
