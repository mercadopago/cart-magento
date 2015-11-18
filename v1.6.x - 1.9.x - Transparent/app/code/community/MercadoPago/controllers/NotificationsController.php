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

class MercadoPago_NotificationsController extends Mage_Core_Controller_Front_Action{

	protected $_return = null;
	protected $_order = null;
	protected $_order_id = null;
	protected $_mpcartid = null;
	protected $_sendemail = false;
	protected $_hash = null;

	public function indexAction(){
		
		$core = Mage::getModel('mercadopago/core');
		
		try {
			$params = $this->getRequest()->getParams();
			
			//notification received
			Mage::helper('mercadopago')->log("Received notification", 'mercadopago-notification.log', $params);
			
			if(isset($params['checkout']) && $params['checkout'] == "standard"){
				Mage::helper('mercadopago')->log("Type: Standard", 'mercadopago-notification.log');
				$this->standard($params);
			}else{
				Mage::helper('mercadopago')->log("Type: Custom", 'mercadopago-notification.log');
				$this->custom($params);
			}
		} catch (Exception $e) {
			Mage::helper('mercadopago')->log("error: " . $e, 'mercadopago-notification.log');
			echo $e;
			
			//caso erro no processo de notificação de pagamento, mercadopago ira notificar novamente.
			header(' ', true, 400);
			exit;
		}	
	
	}

	public function standard($params){
		$core = Mage::getModel('mercadopago/core');
		
		if (isset($params['id']) && isset($params['topic']) && $params['topic'] == 'merchant_order'){
		
			$response = $core->getMerchantOrder($params['id']);
			Mage::helper('mercadopago')->log("Return merchant_order", 'mercadopago-notification.log', $response);
			
			if($response['status'] == 200 || $response['status'] == 201):
				$data = array();
				$merchant_order = $response['response'];
				$order = Mage::getModel('sales/order')->loadByIncrementId($merchant_order["external_reference"]);
				
				if(count($merchant_order['payments']) > 0):
				
					//status final para pagamento com mais de um cartão
					$status_final = "";
					
					foreach($merchant_order['payments'] as $payment):
						$response = $core->getPayment($payment['id']);
						$payment = $response['response']['collection'];
						$data = $this->formatArrayPayment($data, $payment);
						
						
						//caso ele não esteja settado
						if($status_final == ""):
							$status_final = $payment['status'];
						else:
						
							//verifica se o status inicial é igual ao atual
							//para alterar o status da order, todos tem que estarem iguais
							if($status_final != $payment['status']):
								$status_final = false;
							endif;
						endif;
					endforeach;
					
					//atualiza a order com os dados do pagamento
					$this->updateOrder($data);
					
					
					//caso seja false, eles não são iguais, logo não faz nada.
					if($status_final != false):
						$data['status_final'] = $status_final;
						
						//atualiza status da order de acordo com a notificação do pagamento
						$this->setStatusOrder($data);
					endif;
				endif;
			else:
				Mage::helper('mercadopago')->log("Merchant Order not found", 'mercadopago-notification.log');
				throw new Exception('Merchant Order not found');
			endif;
		}
	}

	public function custom($params){
		$core = Mage::getModel('mercadopago/core');
		
		if (isset($params['id']) && isset($params['topic']) && $params['topic'] == 'payment'){
		
			$response = $core->getPayment($params['id']);
			Mage::helper('mercadopago')->log("Return payment", 'mercadopago-notification.log', $response);
			
			if($response['status'] == 200 || $response['status'] == 201):
				$payment = $response['response']['collection'];
				
				//Atualiza informações da order
				$data = $this->formatArrayPayment(array(), $payment);
				$this->updateOrder($data);
				
				//atualiza status da order de acordo com a notificação do pagamento
				$this->setStatusOrder($payment);
				
			else:
				Mage::helper('mercadopago')->log("Payment not found", 'mercadopago-notification.log');
				throw new Exception('Payment not found');
			endif;
		
		}
	}
	
	/*
	* Funcao responsavel por adicionar informação do pagamento no pedido
	*/
	function updateOrder($data){
		try {
			$core = Mage::getModel('mercadopago/core');
			$order = Mage::getModel('sales/order')->loadByIncrementId($data["external_reference"]);
			
			//update info de status no pagamento
			$payment_order = $order->getPayment();
			$payment_order->setAdditionalInformation('status', $data['status']);
			$payment_order->setAdditionalInformation('status_detail', $data['status_detail']);
			$payment_order->setAdditionalInformation('payment_id', $data['id']);
			$payment_order->setAdditionalInformation('transaction_amount', $data['transaction_amount']);
			
			if(isset($data['cardholder_name']))
				$payment_order->setAdditionalInformation('cardholderName', $data['cardholder_name']);
			
			if(isset($data['installments']))
				$payment_order->setAdditionalInformation('installments', $data['installments']);
			
			if(isset($data['payment_method_id']))
				$payment_order->setAdditionalInformation('payment_method', $data['payment_method_id']);
			
			if(isset($data['statement_descriptor']))
				$payment_order->setAdditionalInformation('statement_descriptor', $data['statement_descriptor']);
			
			if(isset($data['trunc_card']))
				$payment_order->setAdditionalInformation('trunc_card', $data['trunc_card']);
			
			$payment_status = $payment_order->save();
			Mage::helper('mercadopago')->log("Update Payment", 'mercadopago-notification.log', $payment_status->toString());
			
			//adiciona informações sobre o comprador na order	
			if ($data['payer_first_name'])
			$order->setCustomerFirstname($data['payer_first_name']);
			
			if ($data['payer_last_name'])
				$order->setCustomerLastname($data['payer_last_name']);
			
			if ($data['payer_email'])
				$order->setCustomerEmail($data['payer_email']);
			
			$status_save = $order->save();
			Mage::helper('mercadopago')->log("Update order", 'mercadopago-notification.log', $status_save->toString());
			
		} catch (Exception $e) {
			Mage::helper('mercadopago')->log("erro in update order status: " . $e, 'mercadopago-notification.log');
			echo $e;
			
			//caso erro no processo de notificação de pagamento, mercadopago ira notificar novamente.
			header(' ', true, 400);
			exit;
		}
	}

	
	function setStatusOrder($payment){
		try {
			$core = Mage::getModel('mercadopago/core');
			Mage::helper('mercadopago')->log("Received Payment data", 'mercadopago-notification.log', $payment);
			
			$message = "";
			$status = "";
			
			// obtem a order para atualizar o status
			$order = Mage::getModel('sales/order')->loadByIncrementId($payment["external_reference"]);
			
			//adiciona variavel de status
			$status = $payment['status'];
			
			//caso exista um status_final, utiliza ele (pagamento com dois cartões)
			if(isset($payment['status_final'])){
				$status = $payment['status_final'];
			}
	
			switch ( $status ) {
				case 'approved':
					//add status na order
					$message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment was approved.');
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_approved');
					
					//cria a invoice
					$invoice = $order->prepareInvoice();
					$invoice->register()->pay();
					Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder())
					->save();
					
					$invoice->sendEmail(true, $message);
				break;
				
				case 'refunded':
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_refunded');
					$message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment was refunded.');
					$order->cancel();
					break;
				
				case 'pending':
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
					$message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment is being processed.');
					break;
				
				case 'authorized':    
				case 'in_process':
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
					$message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment is being processed. Will be approved within 2 business days.');
					break;
				
				case 'in_mediation':
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_in_mediation');
					$message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment is in the process of Dispute, check the graphic account of the MercadoPago for more information.');
					break;
				
				case 'cancelled':              
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_cancelled');
					$message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment was cancelled.');
					$order->cancel();
					break;
				
				case 'rejected':              
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_rejected');
					$message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: The payment was rejected.');
					break;
				
				case 'chargeback':              
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_chargeback');
					$message = Mage::helper('mercadopago')->__('Automatic notification of the MercadoPago: One chargeback was initiated for this payment.');
					break;
				
				default:
					$status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
					$message = '';
			}
			
			//adiciona informações do pagamento para enviar por email e salvar nos historicos
			$message .= Mage::helper('mercadopago')->__('<br/> Payment id: %s', $payment['id']);
			$message .= Mage::helper('mercadopago')->__('<br/> Status: %s', $payment['status']);
			$message .= Mage::helper('mercadopago')->__('<br/> Status Detail: %s', $payment['status_detail']);
			
			$order->addStatusToHistory($status,$message, true);
			$order->sendOrderUpdateEmail(true, $message);
			
			$status_save = $order->save();
			Mage::helper('mercadopago')->log("Update order", 'mercadopago-notification.log', $status_save->toString());
			Mage::helper('mercadopago')->log($message, 'mercadopago-notification.log');
			
			echo $message;
		} catch (Exception $e) {
			Mage::helper('mercadopago')->log("erro in set order status: " . $e, 'mercadopago-notification.log');
			echo $e;
			
			//caso erro no processo de notificação de pagamento, mercadopago ira notificar novamente.
			header(' ', true, 400);
			exit;
		}
	}

/*
* Funcao responsavel por formatar o array para atualizar informações do pedido
*/
	
	function formatArrayPayment($data, $payment){
		$fields = array(
			"status",
			"status_detail",
			"id",
			"payment_method_id",
			"transaction_amount",
			"installments"
		);
		
		foreach($fields as $field){
			if(isset($payment[$field])){
				if(isset($data[$field])){
					$data[$field] .= " | " . $payment[$field];
				}else{
					$data[$field] = $payment[$field];
				}
			}
		}
		
		if(isset($payment["last_four_digits"])){
			if(isset($data["trunc_card"])){
				$data["trunc_card"] .= " | " . "xxxx xxxx xxxx " . $payment["last_four_digits"];
			}else{
				$data["trunc_card"] = "xxxx xxxx xxxx " . $payment["last_four_digits"];
			}    
		}
		
		if(isset($payment['cardholder']['name'])){
			if(isset($data["cardholder_name"])){
				$data["cardholder_name"] .= " | " . $payment["cardholder"]["name"];
			}else{
				$data["cardholder_name"] = $payment["cardholder"]["name"];
			}
		}
		
		if(isset($payment['statement_descriptor']))
			$data['statement_descriptor'] = $payment['statement_descriptor'];
		
		
		//esses dados não precisam concatenar pois se repetem..
		$data['external_reference'] = $payment['external_reference'];
		$data['payer_first_name'] = $payment['payer']['first_name'];
		$data['payer_last_name'] = $payment['payer']['last_name'];
		$data['payer_email'] = $payment['payer']['email'];
		
		return $data;
	}
}