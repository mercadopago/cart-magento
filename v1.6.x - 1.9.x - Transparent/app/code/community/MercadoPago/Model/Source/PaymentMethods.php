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
		
		$methods = array();
		
		//adiciona um valor vazio caso nao queria excluir nada
		$methods[] = array("value" => "", "label" => "");
		
		$client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
		$client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
		
		//verifico se as credenciais não são vazias, caso sejam não é possível obte-los
		if($client_id != "" && $client_secret != ""){
			$mp = new MP($client_id, $client_secret);
			$access_token = $mp->get_access_token();
			
			Mage::helper('mercadopago')->log("Get payment methods by country... ", 'mercadopago.log');
			Mage::helper('mercadopago')->log("API payment methods: " . "/v1/payment_methods?access_token=" . $access_token, 'mercadopago.log');
			$response = MPRestClient::get("/v1/payment_methods?access_token=" . $access_token);
			
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
		}
		
		return $methods;
    }
}