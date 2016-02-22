<?php

class MercadoPago_OneStepCheckout_Model_Observer
{
    /**
     * @param $observer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function successPredispatch($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        $session->getQuote()->setIsActive(false)->save();
        $session->clear();
    }
}
