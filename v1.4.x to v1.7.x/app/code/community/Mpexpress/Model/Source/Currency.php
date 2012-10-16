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
 * @author      Carlos Corrêa (cadu.rcorrea@gmail.com)
 * @copyright  	Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license    	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mpexpress_Model_Source_Currency
{
	public function toOptionArray ()
	{
        return array(
            array('value' => 'ARS', 'label'=>Mage::helper('adminhtml')->__('Pesos Argentinos')),
            array('value' => 'BRL', 'label'=>Mage::helper('adminhtml')->__('Reais')),
            array('value' => 'USD', 'label'=>Mage::helper('adminhtml')->__('Dolares')),
        );
	}
}
###