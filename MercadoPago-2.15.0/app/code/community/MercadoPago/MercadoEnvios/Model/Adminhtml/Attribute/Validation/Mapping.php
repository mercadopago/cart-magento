<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Attribute_Validation_Mapping
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{

    public function save()
    {
        $mappingValues = $this->getValue(); //get the value from our config
        $attributeCodes = array();
        $website = Mage::helper('mercadopago')->getAdminSelectedWebsite();

        if ($website->getConfig('carriers/mercadoenvios/active')) {
            foreach ($mappingValues as $value) {
                if (in_array($value['attribute_code'], $attributeCodes)) {
                    Mage::throwException(Mage::helper('mercadopago')->__("Cannot repeat Magento Product size attributes"));
                }

                $attributeCodes[] = $value['attribute_code'];
            }
        }
        return parent::save();
    }
}