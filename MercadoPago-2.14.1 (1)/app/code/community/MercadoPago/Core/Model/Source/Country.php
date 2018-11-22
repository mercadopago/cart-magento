<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category       Payment Gateway
 * @package        MercadoPago
 * @author         Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 * @copyright      Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MercadoPago_Core_Model_Source_Country
    extends Mage_Payment_Model_Method_Abstract
{
    public function toOptionArray()
    {
        $country = array();
        $country[] = array('value' => "mla", 'label' => Mage::helper('mercadopago')->__("Argentina"), 'code' => 'AR');
        $country[] = array('value' => "mlb", 'label' => Mage::helper('mercadopago')->__("Brasil"), 'code' => 'BR');
        $country[] = array('value' => "mco", 'label' => Mage::helper('mercadopago')->__("Colombia"), 'code' => 'CO');
        $country[] = array('value' => "mlm", 'label' => Mage::helper('mercadopago')->__("Mexico"), 'code' => 'MX');
        $country[] = array('value' => "mlc", 'label' => Mage::helper('mercadopago')->__("Chile"), 'code' => 'CL');
        $country[] = array('value' => "mlv", 'label' => Mage::helper('mercadopago')->__("Venezuela"), 'code' => 'VE');
        $country[] = array('value' => "mpe", 'label' => Mage::helper('mercadopago')->__("Perú"), 'code' => 'PE');
        $country[] = array('value' => "mlu", 'label' => Mage::helper('mercadopago')->__("Uruguay"), 'code' => 'UY');

        //force order by key
        ksort($country);

        return $country;
    }

    public function getCodeByValue($value)
    {
        $countries = $this->toOptionArray();
        foreach ($countries as $country) {
            if ($value == $country['value']) {
                return $country['code'];
            }
        }
        return '';
    }
}
