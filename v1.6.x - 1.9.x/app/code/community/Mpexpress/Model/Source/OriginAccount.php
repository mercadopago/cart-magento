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

require_once(Mage::getBaseDir('lib') . '/mercadopago/mercadopago.php');
 
class Mpexpress_Model_Source_OriginAccount extends Mage_Payment_Model_Method_Abstract{
	public function toOptionArray (){
		
		$response = MPRestClient::get("/sites");
		$response = $response['response'];
		
		foreach($response as $v){
			$sites[] = array('value' => $v['id'], 'label'=>Mage::helper('adminhtml')->__($v['name']));      
		}
		
		return $sites;
	}
}
###