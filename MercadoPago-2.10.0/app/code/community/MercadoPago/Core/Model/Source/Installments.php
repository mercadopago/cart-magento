<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category     Payment Gateway
 * @package      MercadoPago
 * @author       Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 * @copyright    Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class MercadoPago_Core_Model_Source_Installments
    extends Mage_Payment_Model_Method_Abstract
{
    public function toOptionArray()
    {
        $installment = array();

        Mage::helper('mercadopago')->log("Get installments ... ", 'mercadopago.log');

        $installment[] = array("value" => 0, "label" => "N/A");
        $installment[] = array("value" => 1, "label" => "1");
        $installment[] = array("value" => 2, "label" => "2");
        $installment[] = array("value" => 3, "label" => "3");
        $installment[] = array("value" => 4, "label" => "4");
        $installment[] = array("value" => 5, "label" => "5");
        $installment[] = array("value" => 6, "label" => "6");
        $installment[] = array("value" => 9, "label" => "9");
        $installment[] = array("value" => 10, "label" => "10");
        $installment[] = array("value" => 12, "label" => "12");
        $installment[] = array("value" => 15, "label" => "15");
        $installment[] = array("value" => 24, "label" => "24");

        Mage::helper('mercadopago')->log("Installments ... ", 'mercadopago.log', $installment);

        return $installment;
    }
}
