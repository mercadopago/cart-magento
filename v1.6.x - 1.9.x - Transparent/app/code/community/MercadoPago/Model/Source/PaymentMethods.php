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

class MercadoPago_Model_Source_PaymentMethods extends Mage_Payment_Model_Method_Abstract{
	
    public function toOptionArray (){
		
		Mage::helper('mercadopago')->log("Get payment methods by country... ", 'mercadopago.log');
		
		$country = strtoupper(Mage::getStoreConfig('payment/mercadopago/country'));
		$methods = array();
		//adiciona um valor vazio caso nao queria excluir nada
		$methods[] = array("value" => "", "label" => "");
		$response = MPRestClient::get("/sites/" . $country . "/payment_methods");
		Mage::helper('mercadopago')->log("API payment methods", 'mercadopago.log', $response);
		
		$response = $response['response'];
		
		foreach($response as $m){
			if ( $m['id'] != 'account_money' ) {
				$methods[] = array(
					'value' => $m['id'],
					'label'=>Mage::helper('adminhtml')->__($m['name'])
				);
			}
		}
		
		return $methods;
    }
}