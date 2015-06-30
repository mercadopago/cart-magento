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

class MercadoPago_Model_Core extends Mage_Payment_Model_Method_Abstract{
    
    
    protected $_code = 'mercadopago';

    protected $_isGateway                   = true;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = true;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = true;
    protected $_canFetchTransactionInfo     = true;
    protected $_canCreateBillingAgreement   = true;
    protected $_canReviewPayment            = true;
    
	
	 /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get admin checkout session namespace
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getAdminCheckout() {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Retrieves Quote
     *
     * @param integer $quoteId
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId = null) {
        if (!empty($quoteId)) {
            return Mage::getModel('sales/quote')->load($quoteId);
        }
        else {
            if(Mage::app()->getStore()->isAdmin()) {
                return $this->_getAdminCheckout()->getQuote();
            } else {
                return $this->_getCheckout()->getQuote();
            }
        }
    }

    /**
     * Retrieves Order
     *
     * @param integer $incrementId
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder($incrementId) {
        return Mage::getModel('sales/order')->loadByIncrementId($incrementId);
    }
	
	public function getInfoPaymentByOrder($order_id){
		$order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
		$payment = $order->getPayment();
		$info_payments = array();
		$fields = array(
			array("field" => "cardholderName", "title" => "Card Holder Name: %s"),
			array("field" => "trunc_card", "title" => "Card Number: %s"),
			array("field" => "payment_method", "title" => "Payment Method: %s"),
			array("field" => "expiration_date", "title" => "Expiration Date: %s"),
			array("field" => "installments", "title" => "Installments: %s"),
			array("field" => "statement_descriptor", "title" => "Statement Descriptor: %s"),
			array("field" => "payment_id", "title" => "Payment id (MercadoPago): %s"),
			array("field" => "status", "title" => "Payment Status: %s"),
			array("field" => "status_detail", "title" => "Payment Detail: %s"),
			array("field" => "activation_uri", "title" => "Generate Ticket")
		);
		
		foreach($fields as $field):
			if($payment->getAdditionalInformation($field['field']) != ""):
				$text = Mage::helper('mercadopago')->__($field['title'], $payment->getAdditionalInformation($field['field']));
				$info_payments[$field['field']] = array(
					"text" => $text,
					"value" => $payment->getAdditionalInformation($field['field'])
				);
			endif;
		endforeach;
		
		return $info_payments;
	}
	
	protected function validStatusTwoPayments($status){
		$array_status = explode(" | ", $status);
		$status_verif = true;
		$status_final = "";
		foreach($array_status as $status):
		
			if($status_final == ""){
				$status_final = $status;
			}else{
				if($status_final != $status){
					$status_verif = false;
				}
			}
		endforeach;
		
		if($status_verif === false){
			$status_final = "other";
		}
		
		return $status_final;
	}
	
	public function getMessageByStatus($status, $status_detail, $payment_method, $installment, $amount){
		$status = $this->validStatusTwoPayments($status);
		$status_detail = $this->validStatusTwoPayments($status_detail);
		
		$message = array(
			"title" => "",
			"message" => ""
		);
		
		
		switch ($status){
			case "approved":
				$message['title'] = Mage::helper('mercadopago')->__('Done, your payment was accredited!');
				break;
		
			case "in_process":
				$message['title'] = Mage::helper('mercadopago')->__('We are processing the payment.');
				$message['message'] = Mage::helper('mercadopago')->__('In less than 2 business days we will tell you by e-mail if it is accredited or if we need more information.');
				break;
		
			case 'authorized':
			case "pending":
				$message['title'] = Mage::helper('mercadopago')->__('We are processing the payment.');
				$message['message'] = Mage::helper('mercadopago')->__('In less than an hour we will send you by e-mail the result.');
				break;
			
			case "rejected":
				$message['title'] = Mage::helper('mercadopago')->__('We could not process your payment.');
			
				switch ($status_detail){
					case "cc_rejected_bad_filled_card_number":
						$message['message'] = Mage::helper('mercadopago')->__('Check the card number.');
						break;
					
					case "cc_rejected_bad_filled_date":
						$message['message'] = Mage::helper('mercadopago')->__('Check the expiration date.');
						break;
					
					case "cc_rejected_bad_filled_other":
						$message['message'] = Mage::helper('mercadopago')->__('Check the data.');
						break;
					
					case "cc_rejected_bad_filled_security_code":
						$message['message'] = Mage::helper('mercadopago')->__('Check the security code.');
						break;
					
					case "cc_rejected_blacklist":
						$message['message'] = Mage::helper('mercadopago')->__('We could not process your payment.');
						break;
					
					case "cc_rejected_call_for_authorize":
						$message['message'] = Mage::helper('mercadopago')->__('You must authorize to %s the payment of $ %s to MercadoPago.', strtoupper($payment_method), strtoupper($amount));
						break;
					
					case "cc_rejected_card_disabled":
						$message['message'] = Mage::helper('mercadopago')->__('Call %s to activate your card.<br/>The phone is on the back of your card.', strtoupper($payment_method));
						break;
					
					case "cc_rejected_card_error":
						$message['message'] = Mage::helper('mercadopago')->__('We could not process your payment.');
						break;
					
					case "cc_rejected_duplicated_payment":
						$message['message'] = Mage::helper('mercadopago')->__('You already made a payment by that value.<br/>If you need to repay, use another card or other payment method.');
						break;
					
					case "cc_rejected_high_risk":
						$message['message'] = Mage::helper('mercadopago')->__('Your payment was rejected.<br/>Choose another payment method, we recommend cash methods.');
						break;
					
					case "cc_rejected_insufficient_amount":
						$message['message'] = Mage::helper('mercadopago')->__('Your %s do not have sufficient funds.', strtoupper($payment_method));
						break;
					
					case "cc_rejected_invalid_installments":
						$message['message'] = Mage::helper('mercadopago')->__('%s does not process payments in %s installments.', strtoupper($payment_method), $installment);
						break;
					
					case "cc_rejected_max_attempts":
						$message['message'] = Mage::helper('mercadopago')->__('You have got to the limit of allowed attempts.<br/>Choose another card or another payment method.');
						break;
					
					case "cc_rejected_other_reason":
						$message['message'] = Mage::helper('mercadopago')->__('%s did not process the payment.', strtoupper($payment_method));
						break;
				
				}
				
				break;
			case "cancelled":
				$message['title'] = Mage::helper('mercadopago')->__('Payments were canceled.');
				$message['message'] = Mage::helper('mercadopago')->__('Contact for more information.');
				break;
			case "other":
				$message['title'] = Mage::helper('mercadopago')->__('Thank you for your purchase!');
				break;
		}
		
		return $message;
	}
	
	public function getPayment($payment_id){
		$model = $this;
		$this->client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
		$this->client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
		$mp = new MP($this->client_id, $this->client_secret);
		return $mp->get_payment($payment_id);
	}
	
	public function getMerchantOrder($merchant_order_id){
		$model = $this;
		$this->client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
		$this->client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
		$mp = new MP($this->client_id, $this->client_secret);
		
		return $mp->get("/merchant_orders/" . $merchant_order_id);
	}
	
    public function getPaymentMethods(){
		$this->client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
        $this->client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
		
		$mp = new MP ($this->client_id, $this->client_secret);

        $payment_methods = $mp->get("/v1/payment_methods");
	
		return $payment_methods;
    }

	public function getEmailCustomer(){
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$email = $customer->getEmail();
		
		if($email == ""){
			$order = $this->_getOrder();
			$email = $order['customer_email'];
		}
		
		return $email;
	}
	
	
	public function getAmount(){
		$quote = $this->_getQuote();
		$total = $quote->getBaseGrandTotal();
		
		//caso o valor seja null setta um valor 0
		if(is_null($total))
			$total = 0;
		
		return (float) $total;
	}
	
	public function validCoupon($id){
		
		$this->client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
        $this->client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
		
		$mp = new MP ($this->client_id, $this->client_secret);

		$params = array(
			"transaction_amount" => $this->getAmount(),
			"payer_email" => $this->getEmailCustomer(),
			"coupon_code" => $id
		);

        $details_discount = $mp->get("/discount_campaigns", $params);
		
		//add value on return api discount
		$details_discount['response']['transaction_amount'] = $params['transaction_amount'];
		$details_discount['response']['params'] = $params;

		
		return $details_discount;
		
	}
	
}