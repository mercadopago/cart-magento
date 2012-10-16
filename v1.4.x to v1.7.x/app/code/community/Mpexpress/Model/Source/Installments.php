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

class Mpexpress_Model_Source_Installments extends Mage_Payment_Model_Method_Abstract
{
	public function toOptionArray ()
	{
        return array(
            array('value' => '', 'label'=>Mage::helper('adminhtml')->__('Maximum Available')),
            array('value' => '18', 'label'=>Mage::helper('adminhtml')->__('18 Installments')),
            array('value' => '15', 'label'=>Mage::helper('adminhtml')->__('15 Installments')),
            array('value' => '12', 'label'=>Mage::helper('adminhtml')->__('12 Installments')),
            array('value' => '9', 'label'=>Mage::helper('adminhtml')->__('9 Installments')),
            array('value' => '6', 'label'=>Mage::helper('adminhtml')->__('6 Installments')),
            array('value' => '3', 'label'=>Mage::helper('adminhtml')->__('3 Installments')),
            array('value' => '1', 'label'=>Mage::helper('adminhtml')->__('Regular')),
        );
	}
}
