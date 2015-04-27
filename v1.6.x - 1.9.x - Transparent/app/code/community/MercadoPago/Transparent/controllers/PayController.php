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

class MercadoPago_Transparent_PayController extends Mage_Core_Controller_Front_Action{
    
    // /mercadopago_transparent/pay
    public function indexAction(){
	$msg = "";
	$status = false;
	$payment = false;
	$order = false;
	$payment_method_id = false;
	    

	//chama model para fazer o post do pagamento
	$response = Mage::getModel('mercadopago_transparent/transparent')->postPago();
	
	if($response['status'] == 200 || $response['status'] == 201):
	
	    $payment = $response['response'];
	    
	    //set order_id
	    $order = Mage::getModel('sales/order')->loadByIncrementId($payment['external_reference']);
	    
	    //set status
	    $status = $payment['status'];
	    $payment_method_id = $payment['payment_method_id'];
	    
	    switch ($payment['status']){
		case "approved":
		    $msg = "";
		    break;
		case "in_process":
		    $msg = "Em menos de 2 dias úteis você será avisado por e-mail se foi creditado ou se precisarmos de mais informações.";
		    break;
		case "pending":
		    $msg = "Em menos de 1 hora, nós enviaremos o resultado por e-mail.";
		    break;
		case "rejected":
		    
		    switch ($payment['status_detail']){
			case "cc_rejected_bad_filled_card_number":
			    $msg = "Verifique o número do cartão.";
			    break;
			case "cc_rejected_bad_filled_date":
			    $msg = "Verifique a data de validade.";
			    break;
			case "cc_rejected_bad_filled_other":
			    $msg = "Verifique os dados.";
			    break;
			case "cc_rejected_bad_filled_security_code":
			    $msg = "Verifique o código de segurança.";
			    break;
			case "cc_rejected_blacklist":
			    $msg = "Não foi possível processar o pagamento.";
			    break;
			case "cc_rejected_call_for_authorize":
			    $msg = "Você precisa autorizar com a " . strtoupper($payment['payment_method_id']) . " o pagamento de R$" . strtoupper($payment['amount']) . " ao MercadoPago";
			    break;
			case "cc_rejected_card_disabled":
			    $msg = "Ligue para " . strtoupper($payment['payment_method_id']) . " e ative o seu cartão. <br/>
				    O telefone está no verso do seu cartão de crédito.";
			    break;
			case "cc_rejected_card_error":
			    $msg = "Não foi possível processar o pagamento.";
			    break;
			case "cc_rejected_duplicated_payment":
			    $msg = "Você já fez o pagamento deste valor. <br/>
				    Se você precisa pagar novamente use outro cartão ou outro meio de pagamento.";
			    break;
			case "cc_rejected_high_risk":
			    $msg = "O seu pagamento foi recusado. <br/>
				    Recomendamos que você pague com outro dos meios de pagamento oferecidos, preferencialmente à vista.";
			    break;
			case "cc_rejected_insufficient_amount":
			    $msg = "O seu " . strtoupper($payment['payment_method_id']) . " não tem limite suficiente.";
			    break;
			case "cc_rejected_invalid_installments":
			    echo strtoupper($payment['payment_method_id']) . " não processa pagamentos em " . $payment['installments']. " parcelas.";
			    break;
			case "cc_rejected_max_attempts":
			    $msg = "Você atingiu o limite de tentativas permitidas. <br/>
				    Use outro cartão ou outro meio de pagamento.";
			    break;
			case "cc_rejected_other_reason":
			    echo strtoupper($payment['payment_method_id']) . " não processou o pagamento.";
			    break;
		    }
		    
		break;
	    }
	else:
	    $e = "";
	    foreach($response['response']['cause'] as $error):
		
		switch ($error) {
		    case "106":
			$e .=  "Você não pode fazer pagamentos para usuários em outros países. <br/>";
			break;
		    
		    case "109":
			$e .=  "O meio de pagamento selecionado não processa pagamentos as parcelas selecionadas.
Use outro cartão ou outro meio de pagamento. <br/>";
			break;
		    
		    case "126":
			$e .=  "Não foi possível processar o pagamento. Erro de numero: 126.<br/>";
			break;
		    
		    case "129":
			$e .=  "O meio de pagamento selecionado não processa pagamentos do valor selecionado.
Use outro cartão ou outro meio de pagamento.. <br/>";
			break;
		    
		    case "145":
			$e .=  "Não foi possível processar o pagamento. Erro de numero: 145. <br/>";
			break;
		    
		    case "150":
			$e .=  "Você não pode fazer pagamentos. <br/>";
			break;

		    case "151":
			$e .=  "Você não pode fazer pagamentos com esse meio de pagamento. <br/>";
			break;
		    
		    case "160":
			$e .=  "Não foi possível processar o pagamento. Erro de numero: 160. <br/>";
			break;
		    
		    case "204":
			$e .=  "O meio de pagamento selecionado não está disponível neste momento.
Use outro cartão ou outro meio de pagamento. <br/>";
			break;
		    
		    case "801":
			$e .=  "Você realizou um pagamento similar há pouco tempo.
Tente novamente em alguns minutos. <br/>";
			break;
		    
		    default:
			$e .=  "Não foi possível processar o pagamento. <br/>" . json_encode($response['response']). "<br/>";
			break;
		}
	    endforeach;
	    
	    Mage::getSingleton('core/session')->addError('Ocorreu um erro: <br/>' . $e);
	endif;
		
	$this->loadLayout();
	
	//cria um block e adiciona uma view
	$block = $this->getLayout()->createBlock(
	    'Mage_Core_Block_Template',
	    'mercadopago_transparent/sucesso',
	     array('template' => 'mercadopago/transparent/sucesso.phtml')
	);
	
	//envia as informações para view
	$block->assign(
	    array(
		"mensagem"=> $msg,
		"status" => $status,
		"order" => $order,
		"payment_method_id" => $payment_method_id,
		"payment" => $payment
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
