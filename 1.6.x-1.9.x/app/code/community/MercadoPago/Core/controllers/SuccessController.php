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

    /**
     * Send email of new order
     */
    protected function sendNewOrderMail()
    {
        $order = $this->getOrder();
        if ($order->getCanSendNewEmailFlag()) {
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
        $checkoutTypeHandle = $this->getCheckoutHandle();
        $this->loadLayout(['default', $checkoutTypeHandle]);

        $this->_initLayoutMessages('core/session');

        $this->renderLayout();
    }
}
