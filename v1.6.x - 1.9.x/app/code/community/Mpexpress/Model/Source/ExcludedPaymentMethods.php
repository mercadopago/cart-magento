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

class Mpexpress_Model_Source_ExcludedPaymentMethods extends Mage_Payment_Model_Method_Abstract{
	
	public function toOptionArray (){
		$standard = Mage::getModel('mpexpress/Express');
		//$standard = new Mpexpress_Model_Express();
		
		$site = $standard->getConfigData('acc_origin');
		$mp = Mage::getModel('mpexpress/Mp');
		if ( $site != "" ) {
		
			$url = "https://api.mercadolibre.com/sites/$site/payment_methods";
			
			$response = MPRestClient::get("/sites/$site/payment_methods");
			$response = $response['response'];
			
			foreach($response as $v){
				if ( $v['id'] != 'account_money' ) {
					$methods[] = array('value' => $v['id'], 'label'=>Mage::helper('adminhtml')->__($v['name']));
				}
			}
		
		} else {
			$methods[] = array('value' => "", 'label'=>Mage::helper('adminhtml')->__("Please Reload Page"));
		}
		
		return $methods;
	}
}
