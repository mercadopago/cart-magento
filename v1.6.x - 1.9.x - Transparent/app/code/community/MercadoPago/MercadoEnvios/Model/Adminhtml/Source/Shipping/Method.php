<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_Method {

    public function toOptionArray()
    {
        return array(
            array('value'=>73328, 'label'=>Mage::helper('mercadopago')->__('Oca Standard')),
            array('value'=>73330, 'label'=>Mage::helper('mercadopago')->__('Oca Prioritario')),
        );
    }

}