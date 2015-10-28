<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Attribute_Validation_Mapping
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{

    public function save()
    {
        $mappingValues = $this->getValue(); //get the value from our config
        $magentoCodes = [];
        $ocaCodes = [];

        foreach ($mappingValues as $key => $value) {
            if (in_array($value['MagentoCode'], $magentoCodes)) {
                Mage::throwException(Mage::helper('mercadopago')->__("Cannot repeat Magento Product size attributes"));
            }

            if (in_array($value['OcaCode'], $ocaCodes)) {
                Mage::throwException(Mage::helper('mercadopago')->__("Cannot repeat MercadoEnvios Product size attributes"));
            }

            $magentoCodes[] = $value['MagentoCode'];
            $ocaCodes[] = $value['OcaCode'];
        }

        return parent::save();
    }
}