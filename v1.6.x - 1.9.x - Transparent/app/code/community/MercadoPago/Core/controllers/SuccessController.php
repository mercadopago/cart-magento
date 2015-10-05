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

class MercadoPago_Core_SuccessController
    extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $checkoutTypeHandle = $this->getCheckoutHandle();
        $this->loadLayout(['default', $checkoutTypeHandle]);


        $this->_initLayoutMessages('core/session');

        $this->renderLayout();
    }

    public function getCheckoutHandle()
    {
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        $handle = $order->getPayment()->getMethod();
        $handle .= '_success';

        return $handle;
    }
}
