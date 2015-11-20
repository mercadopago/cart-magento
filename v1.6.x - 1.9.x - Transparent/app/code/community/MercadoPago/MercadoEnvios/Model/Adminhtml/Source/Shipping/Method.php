<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_Method
{

    protected $_countryOptions = [
        'mla' => [
            ['value' => 73328, 'label' => 'OCA Estándar'],
            ['value' => 73330, 'label' => 'OCA Prioritario']
        ],
        'mlb' => [
            ['value' => 100009, 'label' => 'Normal'],
            ['value' => 182, 'label' => 'Expresso'],
        ],
        'mlm' => [
            ['value' => 501245, 'label' => 'DHL Estándar'],
            ['value' => 501345, 'label' => 'DHL Express'],
        ]
    ];

    public function toOptionArray()
    {
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        if ($this->_countryOptions[$country]) {
            return $this->_countryOptions[$country];
        }
        return null;
    }

}