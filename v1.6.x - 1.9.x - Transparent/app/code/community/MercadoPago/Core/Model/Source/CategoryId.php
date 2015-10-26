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
class MercadoPago_Core_Model_Source_CategoryId
    extends Mage_Payment_Model_Method_Abstract
{
    public function toOptionArray()
    {
        Mage::helper('mercadopago')->log("Get Categories... ", 'mercadopago.log');

        $response = MercadoPago_Lib_RestClient::get("/item_categories");
        Mage::helper('mercadopago')->log("API item_categories", 'mercadopago.log', $response);

        $response = $response['response'];

        $cat = array();
        $count = 0;
        foreach ($response as $v) {
            //force category others first
            if ($v['id'] == "others") {
                $cat[0] = array('value' => $v['id'], 'label' => Mage::helper('mercadopago')->__($v['description']));
            } else {
                $count++;
                $cat[$count] = array('value' => $v['id'], 'label' => Mage::helper('mercadopago')->__($v['description']));
            }

        };

        //force order by key
        ksort($cat);

        return $cat;
    }
}
