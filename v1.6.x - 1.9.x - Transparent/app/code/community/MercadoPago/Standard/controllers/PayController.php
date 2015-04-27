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

class MercadoPago_Standard_PayController extends Mage_Core_Controller_Front_Action{
    
    // /mercadopago_strandard/pay
    public function indexAction(){
	$init_point = "";
	    

	//chama model para fazer o post do pagamento
	$response = Mage::getModel('mercadopago_standard/checkout')->postPago();
	
	if($response['status'] == 200 || $response['status'] == 201):
	    $payment = $response['response'];
	    $init_point = $payment['init_point'];
	    
	endif;
		
	$this->loadLayout();
	
	//cria um block e adiciona uma view
	$block = $this->getLayout()->createBlock(
	    'Mage_Core_Block_Template',
	    'mercadopago_standard/pay',
	     array('template' => 'mercadopago/standard/pay.phtml')
	);
	
	//envia as informações para view
	$block->assign(
	    array(
		"init_point" => $init_point,
		"type_checkout" => Mage::getModel('mercadopago_standard/checkout')->getConfigData('type_checkout'),
		"iframe_width" => Mage::getModel('mercadopago_standard/checkout')->getConfigData('iframe_width'),
		"iframe_height" => Mage::getModel('mercadopago_standard/checkout')->getConfigData('iframe_height'),
		"banner_checkout" => Mage::getModel('mercadopago_standard/checkout')->getConfigData('banner_checkout')
	    )
	);
	
	//insere o block
	$this->getLayout()->getBlock('content')->append($block);
	$this->_initLayoutMessages('core/session');
	
	//adiciona uma clean page 
	$root = $this->getLayout()->getBlock('root');
	$root->setTemplate("mercadopago/clean_page.phtml");
                
	$this->renderLayout();
    }
    
}
