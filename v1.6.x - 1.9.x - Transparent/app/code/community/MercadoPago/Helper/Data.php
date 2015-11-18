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

class MercadoPago_Helper_Data extends Mage_Payment_Helper_Data{
    
    
    public function log($message, $file = "mercadopago.log", $array = null){
		//pega a configuração de log no admin, essa variavel vem como true por padrão
		$action_log = Mage::getStoreConfig('payment/mercadopago/logs');
		
		//caso tenha um array, transforma em json para melhor visualização
		if(!is_null($array))
			$message .= " - " . json_encode($array);
		
		//set log
		Mage::log($message, null, $file, $action_log);
	}
    
}