<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_FreeMethod
    extends MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_Method
{

    public function toOptionArray()
    {
        $arr = parent::toOptionArray();
        array_unshift($arr, array('value'=>'', 'label'=>Mage::helper('shipping')->__('None')));
        return $arr;
    }

}