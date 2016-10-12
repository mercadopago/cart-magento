<?php

class MercadoPago_Core_Block_Recurring_Pay
    extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('mercadopago/standard/pay.phtml');
    }
}
