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
            'label' => Mage::helper('adminhtml')->__('Product Attribute'),
            'style' => 'width:120px',
        ));

        $this->addColumn('unit', array(
            'label' => Mage::helper('adminhtml')->__('Attribute Unit'),
            'style' => 'width:120px',
        ));

        $this->setTemplate('mercadopago/array_dropdown.phtml');
        parent::__construct();
    }

    protected function _getAttributes()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('is_visible', 1)
            ->addFieldToFilter('frontend_input', array('nin' => array('boolean', 'date', 'datetime', 'gallery', 'image', 'media_image', 'select', 'multiselect', 'textarea')))
            ->load();

        return $attributes;
    }

    protected function _getStoredMappingValues()
    {
        $prevValues = array();
        foreach ($this->getArrayRows() as $key => $_row) {
            $prevValues[$key] = array('attribute_code' => $_row->getData('attribute_code'), 'unit' => $_row->getData('unit'));
        }

        return $prevValues;
    }

    protected function _getMeLabel()
    {
        return array($this->__('Length'), $this->__('Width'), $this->__('Height'), $this->__('Weight'));
    }
}
