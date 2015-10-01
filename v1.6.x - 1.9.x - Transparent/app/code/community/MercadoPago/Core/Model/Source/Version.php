<?php

class MercadoPago_Core_Model_Source_Version
    extends Mage_Payment_Model_Method_Abstract
{
    public function toOptionArray()
    {
        $version = array();
        $version[] = array('value' => "v0", 'label' => 'V0');
        $version[] = array('value' => "v1", 'label' => 'V1');

        return $version;
    }
}
