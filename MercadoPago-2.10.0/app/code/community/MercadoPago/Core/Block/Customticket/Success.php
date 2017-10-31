<?php

class MercadoPago_Core_Block_Customticket_Success
    extends MercadoPago_Core_Block_AbstractSuccess
{
    protected function _construct()
    {
        error_log(" _construct page ticket ----> ");

        parent::_construct();
        $this->setTemplate('mercadopago/custom_ticket/success.phtml');
    }

}
