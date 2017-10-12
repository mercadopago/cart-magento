<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright      Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MercadoPago_Core_Model_Source_ListPages
    extends Mage_Payment_Model_Method_Abstract
{
    public function toOptionArray()
    {
        $pages = array();
        $pages[] = array('value' => "product.info.calculator",  'label' => Mage::helper('mercadopago')->__("Product Detail Page"));
        $pages[] = array('value' => "checkout.cart.calculator", 'label' => Mage::helper('mercadopago')->__("Cart page"));

        //force order by key
        ksort($pages);

        return $pages;
    }
}
