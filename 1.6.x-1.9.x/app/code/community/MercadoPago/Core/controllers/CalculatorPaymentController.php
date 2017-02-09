<?php

class MercadoPago_Core_CalculatorPaymentController
    extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}