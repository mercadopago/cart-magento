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

class MercadoPago_ApiController extends Mage_Core_Controller_Front_Action{

	// action: /mercadopago/api/amount
	
    public function amountAction(){
        
        $core = Mage::getModel('mercadopago/core');

		$response = array(
			"amount" => $core->getAmount()		
		);
		
		header('Content-Type: application/json');
        echo json_encode($response);
		exit;
        
    }
	
	// action: /mercadopago/api/cupom?id=:cupom_id
	
	public function couponAction(){
		$response = array();
		$core = Mage::getModel('mercadopago/core');
		
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != ""){
			$coupon_id = $_REQUEST['id'];
			$response = $core->validCoupon($coupon_id);
		}else{
			$response = array(
				"status" => 400,
				"response" => array(
					"error" => "invalid_id",
					"message" => "invalid id"
				)
			);	
		}
        
		header('Content-Type: application/json');
        echo json_encode($response);
		exit;
    }
	
	
	/*
	 *
	 * Test Request
	 *
	 */
	
	public function testAction(){
		$core = Mage::getModel('mercadopago/core');
		
		$payment_methods = $core->getPaymentMethods();
		
		$response = array(
			"getPaymentMethods"	=> $payment_methods['status'],
			"public_key" => Mage::getStoreConfig('payment/mercadopago_custom/public_key')
		);
		
		header('Content-Type: application/json');
        echo json_encode($response);
		exit;
	}
    
}