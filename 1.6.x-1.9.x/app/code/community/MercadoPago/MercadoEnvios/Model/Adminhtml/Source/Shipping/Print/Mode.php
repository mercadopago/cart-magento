<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_Print_Mode
{

    public function toOptionArray()
    {
        return [['value' => 'pdf' , 'label' => 'PDF'],['value' => 'zpl2' , 'label' => 'ZIP']];
    }

}