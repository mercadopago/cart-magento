<?php


/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
class Mpexpress_IpnController extends Mage_Core_Controller_Front_Action
{
    
    protected $_return = null;
    protected $_order = null;
    protected $_order_id = null;
    protected $_mpcartid = null;
    protected $_sendemail = false;
    protected $_hash = null;

    public function indexAction()
    {
    

    $params = $this->getRequest()->getParams();
    
 
   
    
    if (isset($params['id']) && isset($params['topic'])){
        

         try {
             
            $ipn = Mage::getModel('mpexpress/Checkout');    
            $this->_return = $ipn->GetStatus($params['id']);
      
             if ((int)$this->_return['collection']['id'] === (int)$params['id']) {
                $this->_process_order();
            
            }
     
        } catch (Exception $e) {
            Mage::logException($e);
        }
        
        
    }
    
    } 
    
    private function _process_order()
    {   
      //  $standard = new MercadoPago_Model_Standard();
        $standard = Mage::getModel('mpexpress/Express');  
        $this->_get_order();
 
        
        $config = Mage::getModel('mpexpress/Express');                         
        
        if ($this->_return['collection']['payer']['first_name'])$this->_order->setCustomerFirstname($this->_return['collection']['payer']['first_name']);
        if ($this->_return['collection']['payer']['last_name'])$this->_order->setCustomerLastname($this->_return['collection']['payer']['last_name']);
        if ($this->_return['collection']['payer']['email'])$this->_order->setCustomerEmail($this->_return['collection']['payer']['email']);
        $this->_order->save();

        if($this->_sendemail){
           $name = $this->_return['collection']['payer']['first_name'].' ' .$this->_return['collection']['payer']['last_name'];
           $this->notify($name,$this->_return['collection']['payer']['email']);
        }
        
        switch ( $this->_return['collection']['status']) {
          
          case 'approved':
      
              
             $createinvoice = Mage::getModel('mpexpress/Express')->getConfigData('auto_create_inovice');
             if ($createinvoice == 1){  
             
             // Geração automatica de invoice    
                 
             // checa para ver se já tem invoice    
             if(!$this->_order->hasInvoices()){
             $invoice = $this->_order->prepareInvoice();   
             $invoice->register()->pay();
             Mage::getModel('core/resource_transaction')
                      ->addObject($invoice)
                      ->addObject($invoice->getOrder())
                      ->save();
        
             
             $message = 'Payment '.$invoice->getIncrementId().' was created. MercadoPago automatically confirmed payment for this order.';
             $status = $config->getConfigData('order_status_approved');
             $this->_order->addStatusToHistory(
			$status, //update order status to processing after creating an invoice
			$message,
			true
			);
            $invoice->sendEmail(true, $message);
                } else {  }
            } else {
                
            // Geração não automática de invoice    
            $message = 'MercadoPago automatically confirmed payment for this order.';           
            $status = $config->getConfigData('order_status_approved');
            $this->_order->addStatusToHistory(
			$status, //update order status to processing after creating an invoice
			$message,
			true
			);
            $this->_order->sendOrderUpdateEmail(true, $message); 
            }
         break;
          case 'refunded':
            $status = $config->getConfigData('order_status_refunded');
            $message = 'Payment was refound. The vendor returned the values ​​of this operation.';	
            $this->_order->cancel();
            $this->_order->addStatusToHistory($status, $message);
            $this->_order->sendOrderUpdateEmail(true, $message);
            break;
          case 'pending':
              $status = $config->getConfigData('order_status_in_process');
              $message = 'The user has not completed the payment process yet.';
              $this->_order->addStatusToHistory($status, $message);
              $this->_order->sendOrderUpdateEmail(true, $message);
          case 'in_process':
              $status = $config->getConfigData('order_status_in_process');
              $message = 'The payment is been analysing.';
              $this->_order->addStatusToHistory($status, $message);
              $this->_order->sendOrderUpdateEmail(true, $message);
            break;
          case 'in_mediation':
              $status = $config->getConfigData('order_status_in_mediation');
              $message = 'It started a dispute for the payment.';
              $this->_order->addStatusToHistory($status, $message);
              $this->_order->sendOrderUpdateEmail(true, $message);
            break;
          case 'cancelled':              
              $status = $config->getConfigData('order_status_cancelled');
              $message = 'Payment was canceled.';
              $this->_order->addStatusToHistory($status, $message);
              $this->_order->sendOrderUpdateEmail(true, $message);
              $this->_order->cancel();
             break;
         case 'rejected':              
              $status = $config->getConfigData('order_status_rejected');
              $message = 'Payment was Reject.';
              $this->_order->addStatusToHistory($status, $message);
              $this->_order->sendOrderUpdateEmail(true, $message);
            break;
          default:
            $status = $config->getConfigData('order_status_in_process');
            $message = "";    
            $this->_order->addStatusToHistory($status, $message);
            $this->_order->sendOrderUpdateEmail(true, $message);
            }
          
        
        $this->_order->save();
        
						
    }
    
    private function _get_order()
    {
        if ( empty($this->_order) || $this->_order == null ) {
            $idr = $this->_return['collection']['external_reference'];
            $ida = explode('-',$idr);
            $this->_hash  = $ida[1];           
            
            /// if is normal checkout (order is already created)
            if ($ida[0] == 'mpexpress'){
                
            $preorder = Mage::getModel('sales/order')->loadByIncrementId($this->_hash); 
            if (isset($preorder['increment_id'])){
            $this->_order = $preorder;
            }else{
               echo 'Order not found';
               die;            
            }
            // else, if is checkout express, maybe order is not created
            
            } else {
                
                $mpcart = Mage::getModel('mpexpress/mpcart')->load($this->_hash,'hash');  
                $this->_order_id = $mpcart->getOrderId();
                $this->_mpcartid = $mpcart->getMpexpressCartId();

                // If don´t have order, generate a order and send email
     
                    if(is_null($this->_order_id) || empty($this->order_id)){
      
                    $this->_order_id = $mpcart->generateEmptyOrder($this->_mpcartid);  

                    $preorder = Mage::getModel('sales/order')->loadByIncrementId($this->_order_id); 
                    
                    if (isset($preorder['increment_id'])){
                    $this->_order = $preorder;
                    }else{
                    echo 'Order not found';
                    die;            
                    }
                    
                    $this->_sendemail = true;
                    
                    }else{
                    $preorder = Mage::getModel('sales/order')->loadByIncrementId($this->_order_id); 
                    if (isset($preorder['increment_id'])){
                    $this->_order = $preorder;
                    }else{
                    echo 'Order not found';
                    die;            
                    }
                
                }
                
                
            }
        }

    }
    

    public function notify($sendToName, $sendToEmail) {
 
    
    $store = Mage::app()->getStore();
    $store->getName();
    $link = '<a href="'. Mage::getBaseUrl() . 'mpexpress/information/address/hash/'.$this->_hash.'">'.Mage::getBaseUrl().'mpexpress/information/address/hash/'.$this->_hash.'</a>';
    $name = Mage::getStoreConfig('general/store_information/name');
    $from = Mage::getStoreConfig('trans_email/ident_general/email');
    $subject = Mage::helper('mpexpress')->__('Complete your order shipping information');
    $charset = '<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />';
    $dear = Mage::helper('mpexpress')->__('Thank you for your purchase.');
    $linha = ' <br /> ';        
    $information = Mage::helper('mpexpress')->__('To complete your order, if is not done yet, fill the address information at the address below.');        
    $finalmessage = $charset.$dear.$linha.$linha.$information.$linha.$linha.$link;
    $mail = Mage::getModel('core/email');
    $mail->setToName($sendToName);
    $mail->setToEmail($sendToEmail);
    $mail->setBody($finalmessage);
    $mail->setSubject('=?utf-8?B?'.base64_encode($subject).'?=');
    $mail->setFromEmail($from);
    $mail->setFromName($name);
    $mail->setType('html');
 
    try {
        $mail->send();
    }
    catch (Exception $e) {
        Mage::logException($e);
        return false;
    }
 
    return true;
}

    
    
    
                

             
             
    
     
}
