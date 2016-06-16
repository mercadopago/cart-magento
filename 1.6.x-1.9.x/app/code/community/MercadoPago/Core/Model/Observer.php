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
            "mpe" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
        ],
        "mercadopago_customticket" => [
            "mla" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/2014/230x60.png",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlm" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlc" => "https://secure.mlstatic.com/developers/site/cloud/banners/cl/468x60.gif",
            "mlv" => "https://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg",
            "mpe" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
        ],
        "mercadopago_standard"     => [
            "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlc" => "https://secure.mlstatic.com/developers/site/cloud/banners/cl/468x60.gif",
            "mlv" => "https://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg",
            "mlm" => "http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG",
            "mpe" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
        ]
    ];

    private $available_transparent_credit_cart = ['mla', 'mlb', 'mlm', 'mco', 'mlv', 'mlc', 'mpe'];
    private $available_transparent_ticket = ['mla', 'mlb', 'mlm'];
    private $_website;

    const LOG_FILE = 'mercadopago.log';

    /**
     * @param $observer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkAndValidData($observer)
    {

        $this->_website = Mage::helper('mercadopago')->getAdminSelectedWebsite();

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
        //check if country is available for transparent checkout
        //and disables method if it is not

        $country = $this->_website->getConfig('payment/mercadopago/country');

        if (!in_array($country, $this->available_transparent_credit_cart)) {
            $this->_saveWebsiteConfig('payment/mercadopago_custom/active', 0);
        }

        if (!in_array($country, $this->available_transparent_ticket)) {
            $this->_saveWebsiteConfig('payment/mercadopago_customticket/active', 0);
        }
    }

    public function checkBanner($typeCheckout)
    {
        //get country
        $country = $this->_website->getConfig('payment/mercadopago/country');
        if (!isset($this->banners[$typeCheckout][$country])) {
            return;
        }
        $defaultBanner = $this->banners[$typeCheckout][$country];

        $currentBanner = $this->_website->getConfig('payment/' . $typeCheckout . '/banner_checkout');

        Mage::helper('mercadopago')->log("Type Checkout Path: " . $typeCheckout, self::LOG_FILE);
        Mage::helper('mercadopago')->log("Current Banner: " . $currentBanner, self::LOG_FILE);
        Mage::helper('mercadopago')->log("Default Banner: " . $defaultBanner, self::LOG_FILE);

        if (in_array($currentBanner, $this->banners[$typeCheckout])) {
            Mage::helper('mercadopago')->log("Banner default need update...", self::LOG_FILE);

            if ($defaultBanner != $currentBanner) {
                $this->_saveWebsiteConfig('payment/' . $typeCheckout . '/banner_checkout', $defaultBanner);

                Mage::helper('mercadopago')->log('payment/' . $typeCheckout . '/banner_checkout setted ' . $defaultBanner, self::LOG_FILE);
            }
        }
    }


    public function setSponsor()
    {
        Mage::helper('mercadopago')->log("Sponsor_id: " . $this->_website->getConfig('payment/mercadopago/sponsor_id'), self::LOG_FILE);

        $sponsorId = "";
        Mage::helper('mercadopago')->log("Valid user test", self::LOG_FILE);

        $accessToken = $this->_website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
        Mage::helper('mercadopago')->log("Get access_token: " . $accessToken, self::LOG_FILE);

        $mp = Mage::helper('mercadopago')->getApiInstance($accessToken);
        $user = $mp->get("/users/me");
        Mage::helper('mercadopago')->log("API Users response", self::LOG_FILE, $user);

        if ($user['status'] == 200 && !in_array("test_user", $user['response']['tags'])) {
            $sponsors = [
                'MLA' => 186172525,
                'MLB' => 186175129,
                'MLM' => 186175064,
                'MCO' => 206959966,
                'MLC' => 206959756,
                'MLV' => 206960619,
                'MPE' => 217178514,
            ];
            $countryCode = $user['response']['site_id'];
            
            if (isset($sponsors[$countryCode])) {
                $sponsorId = $sponsors[$countryCode];
            } else {
                $sponsorId = "";
            }
            
            Mage::helper('mercadopago')->log("Sponsor id set", self::LOG_FILE, $sponsorId);
        }
        $this->_saveWebsiteConfig('payment/mercadopago/sponsor_id', $sponsorId);
        Mage::helper('mercadopago')->log("Sponsor saved", self::LOG_FILE, $sponsorId);
    }

    protected function validateAccessToken()
    {
        $accessToken = $this->_website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
        if (!empty($accessToken)) {
            if (!Mage::helper('mercadopago')->isValidAccessToken($accessToken)) {
                Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Custom Checkout: Invalid access token'));
            }
        }
    }

    protected function validateClientCredentials()
    {
        $clientId = $this->_website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = $this->_website->getConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        if (!empty($clientId) && !empty($clientSecret)) {
            if (!Mage::helper('mercadopago')->isValidClientCredentials($clientId, $clientSecret)) {
                Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Classic Checkout: Invalid client id or client secret'));
            }
        }
    }

    protected function _saveWebsiteConfig($path, $value)
    {
        if ($this->_website->getId() == 0) {
            Mage::getConfig()->saveConfig($path, $value);
        } else {
            Mage::getConfig()->saveConfig($path, $value, 'websites', $this->_website->getId());
        }

    }
}
