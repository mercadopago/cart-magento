<?php


class MercadoPago_Core_RecurringPaymentController
    extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $recurring = Mage::getModel('mercadopago/recurring_payment');

        $arrayAssign = $recurring->getRecurringPaymentData();

        $this->loadLayout();

        $block = Mage::app()->getLayout()->createBlock('mercadopago/recurring_pay');

        $block->assign($arrayAssign);

        $this->getLayout()->getBlock('content')->append($block);
        $this->_initLayoutMessages('core/session');

        $root = $this->getLayout()->getBlock('root');
        $root->setTemplate("mercadopago/clean.phtml");

        $this->renderLayout();
    }
}