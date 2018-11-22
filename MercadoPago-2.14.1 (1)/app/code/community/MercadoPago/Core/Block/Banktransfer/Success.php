<?php

class MercadoPago_Core_Block_Banktransfer_Success
    extends MercadoPago_Core_Block_AbstractSuccess
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mercadopago/bank_transfer/success.phtml');
    }

}
