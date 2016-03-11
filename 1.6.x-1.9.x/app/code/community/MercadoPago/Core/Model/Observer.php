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
class MercadoPago_Core_Model_Observer
{
    private $banners = [
        "mercadopago_custom"       => [
            "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlm" => "http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG",
            "mlc" => "https://secure.mlstatic.com/developers/site/cloud/banners/cl/468x60.gif",
            "mlv" => "https://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg",
        ],
        "mercadopago_customticket" => [
            "mla" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/2014/230x60.png",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlm" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlc" => "https://secure.mlstatic.com/developers/site/cloud/banners/cl/468x60.gif",
            "mlv" => "https://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg",
        ],
        "mercadopago_standard"     => [
            "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlc" => "https://secure.mlstatic.com/developers/site/cloud/banners/cl/468x60.gif",
            "mlv" => "https://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg",
            "mlm" => "http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG"
        ]
    ];

    private $available_transparent_credit_cart = ['mla', 'mlb', 'mlm', 'mco', 'mlv', 'mlc'];
    private $available_transparent_ticket = ['mla', 'mlb', 'mlm'];

    const LOG_FILE = 'mercadopago.log';

    /**
     * @param $observer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkAndValidData($observer)
    {
        $this->validateAccessToken();

        $this->validateClientCredentials();

        $this->setSponsor();

        $this->availableCheckout();

        $this->checkBanner('mercadopago_custom');
        $this->checkBanner('mercadopago_customticket');
        $this->checkBanner('mercadopago_standard');
    }


    public function availableCheckout()
    {
        //verifica se o pais selecionado possui integracao para utilizar os checkouts transparents

        $country = Mage::getStoreConfig('payment/mercadopago/country');

        if (!in_array($country, $this->available_transparent_credit_cart)) {
            Mage::getConfig()->saveConfig('payment/mercadopago_custom/active', 0);
        }

        if (!in_array($country, $this->available_transparent_ticket)) {
            Mage::getConfig()->saveConfig('payment/mercadopago_customticket/active', 0);
        }
    }

    public function checkBanner($type_checkout)
    {
        //get country
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        if (!isset($this->banners[$type_checkout][$country])) {
            return;
        }
        $default_banner = $this->banners[$type_checkout][$country];

        $current_banner = Mage::getStoreConfig('payment/' . $type_checkout . '/banner_checkout');

        Mage::helper('mercadopago')->log("Type Checkout Path: " . $type_checkout, self::LOG_FILE);
        Mage::helper('mercadopago')->log("Current Banner: " . $current_banner, self::LOG_FILE);
        Mage::helper('mercadopago')->log("Default Banner: " . $default_banner, self::LOG_FILE);

        if (in_array($current_banner, $this->banners[$type_checkout])) {
            Mage::helper('mercadopago')->log("Banner default need update...", self::LOG_FILE);

            if ($default_banner != $current_banner) {
                Mage::getConfig()->saveConfig('payment/' . $type_checkout . '/banner_checkout', $default_banner);

                Mage::helper('mercadopago')->log('payment/' . $type_checkout . '/banner_checkout setted ' . $default_banner, self::LOG_FILE);
            }
        }
    }


    public function setSponsor()
    {
        Mage::helper('mercadopago')->log("Sponsor_id: " . Mage::getStoreConfig('payment/mercadopago/sponsor_id'), self::LOG_FILE);

        $sponsor_id = "";
        Mage::helper('mercadopago')->log("Valid user test", self::LOG_FILE);

        $access_token = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
        Mage::helper('mercadopago')->log("Get access_token: " . $access_token, self::LOG_FILE);

        $mp = Mage::helper('mercadopago')->getApiInstance($access_token);
        $user = $mp->get("/users/me");
        Mage::helper('mercadopago')->log("API Users response", self::LOG_FILE, $user);

        if ($user['status'] == 200 && !in_array("test_user", $user['response']['tags'])) {

            switch ($user['response']['site_id']) {
                case 'MLA':
                    $sponsor_id = 186172525;
                    break;
                case 'MLB':
                    $sponsor_id = 186175129;
                    break;
                case 'MLM':
                    $sponsor_id = 186175064;
                    break;
                case 'MCO':
                    $sponsor_id = 206959966;
                    break;
                case 'MLC':
                    $sponsor_id = 206959756;
                    break;
                case 'MLV':
                    $sponsor_id = 206960619;
                    break;
                default:
                    $sponsor_id = "";
                    break;
            }

            Mage::helper('mercadopago')->log("Sponsor id setted", self::LOG_FILE, $sponsor_id);
        }

        Mage::getConfig()->saveConfig('payment/mercadopago/sponsor_id', $sponsor_id);
        Mage::helper('mercadopago')->log("Sponsor saved", self::LOG_FILE, $sponsor_id);
    }

    protected function validateAccessToken()
    {
        $accessToken = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
        if (!empty($accessToken)) {
            if (!Mage::helper('mercadopago')->isValidAccessToken($accessToken)) {
                Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Custom Checkout: Invalid access token'));
            }
        }
    }

    protected function validateClientCredentials()
    {
        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        if (!empty($clientId) && !empty($clientSecret)) {
            if (!Mage::helper('mercadopago')->isValidClientCredentials($clientId, $clientSecret)) {
                Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Classic Checkout: Invalid client id or client secret'));
            }
        }
    }
}
