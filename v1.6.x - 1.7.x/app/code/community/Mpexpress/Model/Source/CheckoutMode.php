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

class Mpexpress_Model_Source_CheckoutMode extends Mage_Payment_Model_Method_Abstract
{
	public function toOptionArray ()
	{
        return array(
            array('value' => 'lightbox', 'label'=>Mage::helper('adminhtml')->__('LightBox')),
            array('value' => 'iframe',   'label'=>Mage::helper('adminhtml')->__('Transparent / Iframe')),
            array('value' => 'redirect', 'label'=>Mage::helper('adminhtml')->__('Redirect')),
            );
	}
}
