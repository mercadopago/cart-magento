<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_Method {

    public function toOptionArray($isMultiselect=true)
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('adminhtml')->__('Oca Standard')),
            array('value'=>1, 'label'=>Mage::helper('adminhtml')->__('Oca Prioritario')),
        );
    }

}