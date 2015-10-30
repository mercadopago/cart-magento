<?php

class MercadoPago_MercadoEnvios_Block_Adminhtml_System_Config_Fieldset_Mapping
        extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        $this->addColumn('magento', array(
            'label' => Mage::helper('adminhtml')->__('MercadoEnvíos'),
            'style' => 'width:120px',
        ));
        $this->addColumn('mailchimp', array(
            'label' => Mage::helper('adminhtml')->__('Magento Customer'),
            'style' => 'width:120px',
        ));
        $this->setTemplate('mercadopago/array_dropdown.phtml');
        parent::__construct();
    }

}
