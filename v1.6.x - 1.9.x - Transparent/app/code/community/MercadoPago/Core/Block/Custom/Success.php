<?php

class MercadoPago_Core_Block_Custom_Success
    extends MercadoPago_Core_Block_AbstractSuccess
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mercadopago/custom/success.phtml');
    }

}
