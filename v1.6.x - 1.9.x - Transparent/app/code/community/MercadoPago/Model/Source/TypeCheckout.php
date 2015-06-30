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

require_once(Mage::getBaseDir('lib') . '/mercadopago/mercadopago.php');

class MercadoPago_Model_Source_TypeCheckout extends Mage_Payment_Model_Method_Abstract{
	
    public function toOptionArray (){
    
		$arr = array(
			array("value"=> "iframe", 'label'=>Mage::helper('adminhtml')->__("Iframe")),
			array("value"=> "redirect", 'label'=>Mage::helper('adminhtml')->__("Redirect")),
			array("value"=> "lightbox", 'label'=>Mage::helper('adminhtml')->__("Lightbox"))
		);
		
		return $arr;
    }
}