<?php

/**
 * Class MercadoPago_Core_SuccessController
 */
class MercadoPago_Core_SuccessController
    extends Mage_Core_Controller_Front_Action
{

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

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
        $handle = $order->getPayment()->getMethod();
        $handle .= '_success';

        return $handle;
    }

    public function indexAction()
    {
        $this->sendNewOrderMail();
        if (!Mage::getStoreConfig('payment/mercadopago/use_successpage_mp')) {
            Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastSuccessQuoteId($this->_getQuoteId());
            $this->_redirect('checkout/onepage/success',$this->_request->getParams());
            return;
        }
        $checkoutTypeHandle = $this->getCheckoutHandle();
        $this->loadLayout(['default', $checkoutTypeHandle]);

        $this->_initLayoutMessages('core/session');

        $this->renderLayout();
    }
}
