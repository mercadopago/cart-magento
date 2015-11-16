<?php

class MercadoPago_MercadoEnvios_Model_Adminhtml_Source_Shipping_Method
{

    protected $_countryCodes = [
        'mla' => '1000',
        'mlb' => '01046925',
        'mco' => '',
        'mlm' => '22615',
        'mlc' => '',
        'mlv' => ''
    ];

    public function toOptionArray()
    {
        $cache = Mage::app()->getCache();
        if (!$cache->load('me_shipping_methods')) {
            $client_id = Mage::getStoreConfig('payment/mercadopago_standard/client_id');
            $client_secret = Mage::getStoreConfig('payment/mercadopago_standard/client_secret');
            $country = Mage::getStoreConfig('payment/mercadopago/country');
            $mp = Mage::helper('mercadopago')->getApiInstance($client_id, $client_secret);
            $response = $mp->get("/shipping_options", ['dimensions' => '30x30x30,500', 'zip_code' => $this->_countryCodes[$country]]);
            if ($response['status'] == 200) {
                $options = [];
                foreach ($response['response']['options'] as $option) {
                    $opt['value'] = $option['shipping_method_id'];
                    $opt['label'] = $option['name'];
                    $options[] = $opt;
                }
                $cache->save(serialize($options), 'me_shipping_methods', [Mage_Core_Model_Config::CACHE_TAG]);
            }
        }

        return unserialize($cache->load('me_shipping_methods'));
    }

}