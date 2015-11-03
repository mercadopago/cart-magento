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
        $this->setTemplate('mercadopago/array_dropdown.phtml');
        parent::__construct();
    }

}
