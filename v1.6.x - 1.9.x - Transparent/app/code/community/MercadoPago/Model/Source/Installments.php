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

class MercadoPago_Model_Source_Installments extends Mage_Payment_Model_Method_Abstract{
	
    public function toOptionArray (){
        
        $inst = array();
		
		Mage::helper('mercadopago')->log("Get Site payment methods... ", 'mercadopago.log');
        $response = MPRestClient::get("/sites/MLB/payment_methods/melicard?marketplace=NONE");
		Mage::helper('mercadopago')->log("API sites payment methods", 'mercadopago.log', $response);
		
        $response = $response['response'];
        
        foreach($response['payer_costs'] as $i){
            
            $inst[] = array(
                'value' => $i['installments'],
                'label'=>Mage::helper('adminhtml')->__($i['installments'] . " Parcela(s)")
            );
        }
        
        return $inst;
    }
}