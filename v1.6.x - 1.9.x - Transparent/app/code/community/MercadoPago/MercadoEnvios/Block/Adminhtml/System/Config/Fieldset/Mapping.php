<?php

class MercadoPago_MercadoEnvios_Block_Adminhtml_System_Config_Fieldset_Mapping
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        $this->addColumn('mercadoenvios', array(
            'label' => Mage::helper('adminhtml')->__('MercadoEnvíos'),
            'style' => 'width:120px',
        ));
        $this->addColumn('magentoproduct', array(
            'label' => Mage::helper('adminhtml')->__('Magento Product Attribute'),
            'style' => 'width:120px',
        ));

        $this->addColumn('unit', array(
            'label' => Mage::helper('adminhtml')->__('Magento Attribute Unit'),
            'style' => 'width:120px',
        ));

        $this->setTemplate('mercadopago/array_dropdown.phtml');
        parent::__construct();
    }

    protected function getMagentoAttributes()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('is_visible', 1)
            ->addFieldToFilter('frontend_input', 'text')
            ->load();

        return $attributes;
    }

    protected function getStoredMappingValues()
    {
        $prevValues = [];
        foreach ($this->getArrayRows() as $_rowId => $_row) {
            $prevValues[] = ['MagentoCode' => $_row->getData('MagentoCode'), 'OcaCode' => $_row->getData('OcaCode'), 'Unit' => $_row->getData('Unit')];
        }

        return $prevValues;
    }
}
