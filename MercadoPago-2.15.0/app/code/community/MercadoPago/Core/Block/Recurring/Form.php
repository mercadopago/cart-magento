<?php

class MercadoPago_Core_Block_Recurring_Form
    extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('mercadopago/standard/form.phtml');
    }
}
