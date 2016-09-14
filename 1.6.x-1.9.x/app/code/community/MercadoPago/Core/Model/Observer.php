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

    public function salesOrderBeforeCancel (Varien_Event_Observer $observer) {
        $orderID = (int) $observer->getEvent()->getControllerAction()->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderID);
        $event = $observer->getEvent();
        if ($order->getExternalRequest()) {
            return;
        }

        $refundAvailable = Mage::getStoreConfig('payment/mercadopago/refund_available');
        $orderStatus = $order->getData('status');
        $orderPaymentStatus = $order->getPayment()->getData('additional_information')['status'];

        $paymentID = $order->getPayment()->getData('additional_information')['id'];
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();

        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);

        $isCreditCardPayment = ($order->getPayment()->getData('additional_information')['installments'] != null ? true : false);

        //si todavia no se registró un pago, no tengo que hacer nada de mercadopago
        if ($paymentID == null) {
            return;
        }

        if (!($paymentMethod == 'mercadopago_standard' || $paymentMethod == 'mercadopago_custom')) {
            $this->_getSession()->addError(__('El pago de la orden no fue realizado mediante MercadoPago. La cancelación se hará a traves de Magento.'));
            return;
        }

        if (!$refundAvailable) {
            $this->_getSession()->addError(__('Las cancelaciones de MercadoPago están deshabilitadas. La cancelación se hará a traves de Magento.'));
            return;
        }

        if (!($orderStatus == 'processing' || $orderStatus == 'pending')) {
            $this->_getSession()->addError(__('Solo se pueden hacer cancelaciones sobre ordenes cuyo estado sea "En proceso" o "Pendiente"'));
            $this->throwCancelationException($observer);
            return;
        }

        if (!($orderPaymentStatus == 'pending' || $orderPaymentStatus == 'in_process' || $orderPaymentStatus == 'rejected' )) {
            $this->_getSession()->addError(__('Solo se pueden hacer cancelaciones sobre ordenes cuyo estado de pago sea "Rechazado", "Pendiente" o "En Proceso"'));
            $this->throwCancelationException();
            return;
        }

        $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
        $response = null;

        $access_token = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);

        if ($paymentMethod == 'mercadopago_standard') {
            $response = $mp->cancel_payment($paymentID);
        } else {
            $data = [
                "status" => 'cancelled',
            ];
            $response = $mp->put("/v1/payments/$paymentID?access_token=$access_token", $data);
        }

        if ($response['status'] == 200) {
            Mage::register('mercadopago_cancellation', true);
            $this->_getSession()->addSuccess(__('Cancelación efectuada mediante MercadoPago'));
        } else {
            $this->_getSession()->addError(__('Error al efectuar la cancelación mediante MercadoPago'));
            $this->_getSession()->addError($response['status'] . ' ' . $response['response']['message']);
            $this->throwCancelationException();
        }
    }

    protected function throwCancelationException () {
        Mage::register('cancel_exception', true);
    }

    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session');
    }

    public function salesOrderAfterCancel (Varien_Event_Observer $observer) {
        $mpCancellation = Mage::registry('mercadopago_cancellation');
        if ($mpCancellation) {
            $order = $observer->getData('order');
            Mage::unregister('mercadopago_cancellation');
            $status = Mage::getStoreConfig('payment/mercadopago/order_status_cancelled');
            $order->setState($status, true);
        }
    }

    public function salesOrderBeforeSave (Varien_Event_Observer $observer) {
        $cancelException = Mage::registry('cancel_exception');
        if ($cancelException) {
            Mage::unregister('cancel_exception');
            Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Cancelación no efectuada'));
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */

    public function creditMemoRefundBeforeSave (Varien_Event_Observer $observer)
    {
        $creditMemo = $observer->getData('creditmemo');
        $order = $creditMemo->getOrder();
        if ($order->getExternalRequest()) {
            return; // si la peticion de crear un credit memo viene de mercado pago, no hace falta mandar el request nuevamente
        }
        $maxDays = (int) Mage::getStoreConfig('payment/mercadopago/maximum_days_refund');
        $maxRefunds = (int) Mage::getStoreConfig('payment/mercadopago/maximum_partial_refunds');
        $refundAvailable = Mage::getStoreConfig('payment/mercadopago/refund_available');
        $orderStatus = $order->getData('status');
        $orderPaymentStatus = $order->getPayment()->getData('additional_information')['status'];
        $payment = $order->getPayment();
        $paymentID = $order->getPayment()->getData('additional_information')['id'];
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $orderStatusHistory = $order->getAllStatusHistory();
        $isCreditCardPayment = ($order->getPayment()->getData('additional_information')['installments'] != null ? true : false);

        $paymentDate = null;
        foreach ($orderStatusHistory as $status) {
            if (strpos ($status->getComment(), 'The payment was approved')) {
                $paymentDate = $status->getCreatedAt();
                break;
            }
        }

        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);

        $isTotalRefund = $payment->getAmountPaid() == $payment->getAmountRefunded();

        if (!($paymentMethod == 'mercadopago_standard' || $paymentMethod == 'mercadopago_custom')) {
            $this->_getSession()->addError(__('El pago de la orden no fue realizado mediante MercadoPago. La devolución se hará a traves de Magento.'));
            return;
        }

        if (!$refundAvailable) {
            $this->_getSession()->addError(__('Las devoluciones de MercadoPago están deshabilitadas. La devolución se hará a traves de Magento.'));
            return;
        }

        if (!$isCreditCardPayment) {
            $this->_getSession()->addError(__('Solo se pueden hacer devoluciones sobre ordenes pagadas con tarjeta de credito'));
            $this->throwRefundException();
        }

        if (!($orderStatus == 'processing' || $orderStatus == 'completed')) {
            $this->_getSession()->addError(__('Solo se pueden hacer devoluciones sobre ordenes cuyo estado sea "En proceso" o "Completada"'));
            $this->throwRefundException();
        }

        if (!($orderPaymentStatus == 'approved')) {
            $this->_getSession()->addError(__('Solo se pueden hacer devoluciones sobre ordenes cuyo estado de pago sea "Aprobado"'));
            $this->throwRefundException();
        }

        if (!($this->daysSince($paymentDate) < $maxDays)) {
            $this->_getSession()->addError(__('Las devoluciones son aceptadas hasta ') .
                $maxDays . __(' días después de aprobado el pago. La orden actual sobrepasa el límite establecido'));
            $this->throwRefundException();
        }

        if (!(count($order->getCreditmemosCollection()->getItems()) < $maxRefunds)) {
            $this->_getSession()->addError(__('Solo se pueden efectuar ' . $maxRefunds . ' devoluciones parciales sobre la misma orden'));
            $this->throwRefundException();
        } else {
            $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
            $response = null;
            $amount = $creditMemo->getGrandTotal();
            $access_token = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
            if ($paymentMethod == 'mercadopago_standard') {
                if ($isTotalRefund) {
                    $response = $mp->refund_payment($paymentID);
                    $order->setMercadoPagoRefundType('total');
                } else {
                    $order->setMercadoPagoRefundType('partial');
                    $metadata = [
                        "reason" => '',
                        "external_reference" => $order->getIncrementId(),
                    ];
                    $params = [
                        "amount" => $amount,
                        "metadata" => $metadata,
                    ];
                    $response = $mp->post("/collections/$paymentID/refunds?access_token=$access_token", $params);
                }
            } else {
                if ($isTotalRefund) {
                    $response = $mp->post("/v1/payments/$paymentID/refunds?access_token=$access_token");
                } else {
                    $params = [
                        "amount" => $amount,
                    ];
                    $response = $mp->post("/v1/payments/$paymentID/refunds?access_token=$access_token", $params);
                }
            }

            if ($response['status'] == 201) {
                $order->setMercadoPagoRefund(true);
                $this->_getSession()->addSuccess(__('Devolución efectuada mediante MercadoPago'));
            } else {
                $this->_getSession()->addError(__('Error al efectuar la devolución mediante MercadoPago'));
                $this->_getSession()->addError($response['status'] . ' ' . $response['response']['message']);
                $this->throwRefundException();
            }
        }
    }

    protected function throwRefundException () {
        Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Devolución no efectuada'));
    }
    
    private function daysSince($date)
    {
        $now = Mage::getModel('core/date')->timestamp(time());
        $date = strtotime ($date);
        return (abs($now - $date) / 86400);
    }

    public function creditMemoRefundAfterSave (Varien_Event_Observer $observer)
    {
        $creditMemo = $observer->getData('creditmemo');

        $status = Mage::getStoreConfig('payment/mercadopago/order_status_refunded');

        $order = $creditMemo->getOrder();
        if ($order->getMercadoPagoRefund() || $order->getExternalRequest()) {
            if ($order->getMercadoPagoRefundType() == 'partial' || $order->getExternalType() == 'partial') {
                if ($order->getState() != $status) {
                    $order->setState($status, true);
                }
            }
        }
    }
}
