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
    private $available_transparent_ticket = ['mla', 'mlb', 'mlm', 'mco', 'mlv', 'mlc', 'mpe'];
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

        $this->validateRecurringClientCredentials();

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

        if ($user['status'] == 200 && !in_array("test_user", $user['response']['tags']) && strpos($accessToken, 'TEST') === FALSE) {
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
        
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        
        if ($order->getExternalRequest() || !$this->_isMercadoPago($paymentMethod)) {
            return;
        }
        $orderStatus = $order->getData('status');
        $orderPaymentStatus = $order->getPayment()->getData('additional_information')['status'];

        $paymentID = $order->getPayment()->getData('additional_information')['payment_id_detail'];

        if (!($orderPaymentStatus == null || $paymentID == null)) {

            $isValidBasicData = $this->checkCancelationBasicData($paymentID, $paymentMethod);
            if ($isValidBasicData) {
                $isValidaData = $this->checkCancelationData($orderStatus, $orderPaymentStatus);

                if ($isValidBasicData && $isValidaData) {
                    $this->_sendCancellationRequest($paymentMethod, $paymentID);
                }
            }
        }
    }

    protected function _sendCancellationRequest ($paymentMethod, $paymentID) {
        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);

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
            $this->_getSession()->addSuccess(__('Cancellation made by Mercado Pago'));
        } else {
            $this->_getSession()->addError(__('Failed to make the cancellation by Mercado Pago'));
            $this->_getSession()->addError($response['status'] . ' ' . $response['response']['message']);
            $this->throwCancelationException();
        }
    }

    protected function checkCancelationBasicData ($paymentID, $paymentMethod) {

        if ($paymentID == null) {
            return false;
        }

        if (!($paymentMethod == 'mercadopago_standard' || $paymentMethod == 'mercadopago_custom')) {
            $this->_getSession()->addWarning(__('Order payment wasn\'t made by Mercado Pago. The cancellation will be made through Magento'));
            return false;
        }

        $refundAvailable = Mage::getStoreConfig('payment/mercadopago/refund_available');
        if (!$refundAvailable) {
            $this->_getSession()->addWarning(__('Mercado Pago cancellations are disabled. The cancellation will be made through Magento'));
            return false;
        }

        return true;
    }

    protected function checkCancelationData ($orderStatus, $orderPaymentStatus) {
        $isValidaData = true;

        if (!($orderStatus == 'processing' || $orderStatus == 'pending')) {
            $this->_getSession()->addError(__('You can only make cancellation on orders whose status is Processing or Pending'));
            $isValidaData = false;
        }

        if (!($orderPaymentStatus == 'pending' || $orderPaymentStatus == 'in_process' || $orderPaymentStatus == 'rejected' )) {
            $this->_getSession()->addError(__('You can only make cancellations on orders whose payment status is Rejected, Pending o In Process'));
            $isValidaData = false;
        }

        if (!$isValidaData) {
            $this->throwCancelationException();
        }

        return $isValidaData;
    }

    protected function throwCancelationException () {
        if (Mage::registry('cancel_exception') != null) {
            Mage::register('cancel_exception', true);
        }
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

    public function salesOrderBeforeSave () {
        $cancelException = Mage::registry('cancel_exception');
        if ($cancelException) {
            Mage::unregister('cancel_exception');
            Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Cancellation not made'));
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */

    public function creditMemoRefundBeforeSave (Varien_Event_Observer $observer)
    {
        $creditMemo = $observer->getData('creditmemo');
        $order = $creditMemo->getOrder();

        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();

        if ($order->getExternalRequest() || !$this->_isMercadoPago($paymentMethod)) {
            return; // si la peticion de crear un credit memo viene de mercado pago, no hace falta mandar el request nuevamente
        }

        $orderStatus = $order->getData('status');
        $orderPaymentStatus = $order->getPayment()->getData('additional_information')['status'];
        $payment = $order->getPayment();

        $orderStatusHistory = $order->getAllStatusHistory();
        $isCreditCardPayment = ($order->getPayment()->getData('additional_information')['installments'] != null ? true : false);

        $paymentDate = null;
        foreach ($orderStatusHistory as $status) {
            if (strpos($status->getComment(), 'The payment was approved') !== false) {
                $paymentDate = $status->getCreatedAt();
                break;
            }
        }
        $isValidBasicData = $this->checkRefundBasicData ($paymentMethod, $paymentDate);
        if ($isValidBasicData) {
            $isValidaData = $this->checkRefundData ($isCreditCardPayment,
                $orderStatus,
                $orderPaymentStatus,
                $paymentDate,
                $order);

            $isTotalRefund = $payment->getAmountPaid() == $payment->getAmountRefunded();
            if ($isValidBasicData && $isValidaData) {
                $this->sendRefundRequest($order, $creditMemo, $paymentMethod, $isTotalRefund);
            }
        }
    }

    protected function checkRefundBasicData ($paymentMethod, $paymentDate) {
        $refundAvailable = Mage::getStoreConfig('payment/mercadopago/refund_available');

        if ($paymentDate == null) {
            $this->_getSession()->addError(__('No payment is recorded. You can\'t make a refund on a unpaid order'));
            return false;
        }

        if (!($paymentMethod == 'mercadopago_standard' || $paymentMethod == 'mercadopago_custom')) {
            $this->_getSession()->addWarning(__('Order payment wasn\'t made by Mercado Pago. The refund will be made through Magento'));
            return false;
        }

        if (!$refundAvailable) {
            $this->_getSession()->addWarning(__('Mercado Pago refunds are disabled. The refund will be made through Magento'));
            return false;
        }

        return true;
    }

    protected function checkRefundData ($isCreditCardPayment,
                                        $orderStatus,
                                        $orderPaymentStatus,
                                        $paymentDate,
                                        $order)
    {

        $maxDays = (int) Mage::getStoreConfig('payment/mercadopago/maximum_days_refund');
        $maxRefunds = (int) Mage::getStoreConfig('payment/mercadopago/maximum_partial_refunds');

        $isValidaData = true;

        if (!$isCreditCardPayment) {
            $this->_getSession()->addError(__('You can only refund orders paid by credit card'));
            $isValidaData = false;
        }

        if (!($orderStatus == 'processing' || $orderStatus == 'completed')) {
            $this->_getSession()->addError(__('You can only make refunds on orders whose status is Processing or Completed'));
            $isValidaData = false;
        }

        if (!($orderPaymentStatus == 'approved')) {
            $this->_getSession()->addError(__('You can only make refunds on orders whose payment status Approved'));
            $isValidaData = false;
        }

        if (!($this->daysSince($paymentDate) < $maxDays)) {
            $this->_getSession()->addError(__('Refunds are accepted up to ') .
                $maxDays . __(' days after payment approval. The current order exceeds the limit set'));
            $isValidaData = false;
        }

        if (!(count($order->getCreditmemosCollection()->getItems()) < $maxRefunds)) {
            $isValidaData = false;
            $this->_getSession()->addError(__('You can only make ' . $maxRefunds . ' partial refunds on the same order'));
        }

        if (!$isValidaData) {
            $this->throwRefundException();
        }

        return $isValidaData;
    }

    protected function sendRefundRequest ($order, $creditMemo, $paymentMethod, $isTotalRefund) {

        $response = null;
        $amount = $creditMemo->getGrandTotal();
        if ($paymentMethod == 'mercadopago_standard') {
            $paymentID = $order->getPayment()->getData('additional_information')['id'];
            $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
            $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
            $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
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
                $response = $mp->post('/collections/' . $paymentID . '/refunds?access_token=' . $mp->get_access_token(), $params);
            }
        } else {
            $paymentID = $order->getPayment()->getData('additional_information')['payment_id_detail'];
            $accessToken = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
            $mp = Mage::helper('mercadopago')->getApiInstance($accessToken);
            if ($isTotalRefund) {
                $response = $mp->post("/v1/payments/$paymentID/refunds?access_token=$accessToken", []);
            } else {
                $params = [
                    "amount" => $amount,
                ];
                $response = $mp->post("/v1/payments/$paymentID/refunds?access_token=$accessToken", $params);
            }
        }

        if ($response['status'] == 201 || $response['status'] == 200) {
            $order->setMercadoPagoRefund(true);
            $this->_getSession()->addSuccess(__('Refund made by Mercado Pago'));
        } else {
            $this->_getSession()->addError(__('Failed to make the refund by Mercado Pago'));
            $this->_getSession()->addError($response['status'] . ' ' . $response['response']['message']);
            $this->throwRefundException();
        }
    }

    protected function throwRefundException () {
        Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Refund not made'));
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

    public function checkoutSubmitAllAfter (Varien_Event_Observer $observer) {
        $recurringProfiles = $observer->getRecurringProfiles();
        if (isset($recurringProfiles) && count($recurringProfiles) > 0) {
            $checkoutSession = Mage::getSingleton('checkout/session');
            $checkoutSession->setRedirectUrl(Mage::getUrl('mercadopago/recurringPayment'));
        }
    }

    protected function validateRecurringClientCredentials()
    {
        $clientId = Mage::getStoreConfig('payment/mercadopago_recurring/client_id');
        $clientSecret = Mage::getStoreConfig('payment/mercadopago_recurring/client_secret');
        if (!empty($clientId) && !empty($clientSecret)) {
            if (!Mage::helper('mercadopago')->isValidClientCredentials($clientId, $clientSecret)) {
                Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Recurring Payment Checkout: Invalid client id or client secret'));
            }
        }
    }

    protected function _isMercadoPago($paymentMethod)
    {
        return ($paymentMethod == 'mercadopago_standard' || $paymentMethod == 'mercadopago_custom');
    }
  
}
