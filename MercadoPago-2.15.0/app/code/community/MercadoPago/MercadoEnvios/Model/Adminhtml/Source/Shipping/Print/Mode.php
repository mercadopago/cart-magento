<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_Print_Mode
{

    public function toOptionArray()
    {
        return array(
          array('value' => 'pdf' , 'label' => 'PDF'),
          array('value' => 'zpl2' , 'label' => 'ZIP')
        );
    }

}
