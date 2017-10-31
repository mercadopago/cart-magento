<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_Method
{

    protected $_countryOptions = array(
        'mla' => array(
            array('value' => 73328, 'label' => 'Normal'),
            array('value' => 73330, 'label' => 'Prioritario')
        ),
        'mlb' => array(
            array('value' => 100009, 'label' => 'Normal'),
            array('value' => 182, 'label' => 'Expresso'),
        ),
        'mlm' => array(
            array('value' => 501245, 'label' => 'DHL Estándar'),
            array('value' => 501345, 'label' => 'DHL Express'),
        )
    );

    public function toOptionArray()
    {
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        if ($this->_countryOptions[$country]) {
            return $this->_countryOptions[$country];
        }
        return null;
    }

    public function getAvailableCodes() {
        $methods = $this->toOptionArray();
        $codes = array();
        foreach ($methods as $method) {
            $codes[] = $method['value'];
        }
        return $codes;
    }

}
