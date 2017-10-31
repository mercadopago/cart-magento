<?php

/**
 * Class MercadoPago_Core_CheckoutController
 */
class MercadoPago_Core_CheckoutController
    extends Mage_Core_Controller_Front_Action
{

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    protected $_statusHelper;

    protected $_core;

    const LOG_FILE = 'mercadopago-checkout-redirect.log';

    const SUCCESS_PAGE_MAGENTO = 'checkout/onepage/success';

    const FAILURE_PAGE_MAGENTO = 'checkout/onepage/failure';


    /**
     * @return Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        if (empty($this->_order)) {
            $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        }

        return $this->_order;
    }

    protected function _getQuoteId() {
        return  Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastQuoteId();
    }

    /**
     * Send email of new order
     */
    protected function sendNewOrderMail()
    {
        $order = $this->getOrder();
        if ($order->getCanSendNewEmailFlag() && !$order->getEmailSent()) {
            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Return handle name depending on payment method
     *
     * @return string
     */
    protected function getCheckoutHandle()
    {
        $order = $this->getOrder();
        if (!$order->getId()){
            return '';
        } else {
            $handle = $order->getPayment()->getMethod();
            $handle .= '_success';

            return $handle;
        }
    }

    protected function _getTotal($order){
        $total = $order->getBaseGrandTotal();
        if (!$total) {
            $total = $order->getBasePrice() + $order->getBaseShippingAmount();
        }
        $total = number_format($total, 2, '.', '');
        return $total;
    }

    public function pageAction()
    {
        $this->_statusHelper = Mage::helper('mercadopago/statusUpdate');
        $this->_core = Mage::getModel('mercadopago/core');

        $this->sendNewOrderMail();

        if (!Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_USE_SUCCESSPAGE_MP)) {
            Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastSuccessQuoteId($this->_getQuoteId());

            $order = $this->getOrder();
            $infoPayment = $this->_core->getInfoPaymentByOrder($order->getIncrementId());
            $payment = $order->getPayment();
            $status = $payment->getAdditionalInformation('status');

            //checkout Custom Ticket
            if (isset($infoPayment['activation_uri'])){
                if (!empty($infoPayment['payment_id_detail']['value'])){
                    $this->_redirect(self::SUCCESS_PAGE_MAGENTO, $this->_request->getParams());
                    return;
                }
                else{
                    $this->_redirect(self::FAILURE_PAGE_MAGENTO, $this->_request->getParams());
                    return;
                }
            }

            if (empty($infoPayment['status']['value'])){
                //checkout Classic
                $merchantOrderId = Mage::app()->getRequest()->getParam('merchant_order_id');
                $response = $this->_core->getMerchantOrder($merchantOrderId);

                if ($response['status'] == 201 || $response['status'] == 200) {
                    $merchantOrderData = $response['response'];
                    $paymentData = $this->_statusHelper->getDataPayments($merchantOrderData, self::LOG_FILE);
                    $status = $paymentData['status'];
                }
            }

            if ($status == 'approved' || $status == 'pending'){
                $this->_redirect(self::SUCCESS_PAGE_MAGENTO, $this->_request->getParams());
                return;
            } else {
                $this->_redirect(self::FAILURE_PAGE_MAGENTO, $this->_request->getParams());
                return;
            }

        }

        //set data for mp analytics
        Mage::register('mp_analytics_data', Mage::helper('mercadopago')->getAnalyticsData($this->getOrder()));
        $checkoutTypeHandle = $this->getCheckoutHandle();

        $this->loadLayout(array('default', $checkoutTypeHandle));

        $this->_initLayoutMessages('core/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($this->getOrder()->getId())));

        $this->renderLayout();
    }

}
