<?php
/**
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL).
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @category   	Payment Gateway
* @package    	MercadoPago
* @author      	Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
* @copyright  	Copyright (c) MercadoPago [http://www.mercadopago.com]
* @license    	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/


class MercadoPago_Core_Model_Source_AddressStreet extends Mage_Payment_Model_Method_Abstract
{
    public function toOptionArray()
    {
        $arr = array(
            array("value"=> "street_1", 'label'=> "Street Address 1"),
            array("value"=> "street_2", 'label'=> "Street Address 2"),
            array("value"=> "street_3", 'label'=> "Street Address 3"),
            array("value"=> "street_3", 'label'=> "Street Address 4")
        );

        return $arr;
    }
}
