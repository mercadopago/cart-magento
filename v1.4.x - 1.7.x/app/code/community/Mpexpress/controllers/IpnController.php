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
               
        
        
        switch ( $this->_return['collection']['status']) {
          
          case 'approved':
      
              
             $createinvoice = Mage::getModel('mpexpress/Express')->getConfigData('auto_create_inovice');
             if ($createinvoice = 1){  
             
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
            $id  = $ida[1];           
            
            $preorder = Mage::getModel('sales/order')->loadByIncrementId($id); 
            if (isset($preorder['increment_id'])){
            $this->_order = $preorder;
            }else{
               echo 'Order not found';
               die;            
            }
        }

    }
    
    
    // função desativada
    private function checkOrderHasAnInvoice($order_entity_id)
    {                
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = "SELECT * FROM `sales_order_entity_int` WHERE `attribute_id` = 329 AND `value` = $order_entity_id;"; // attribute_id = 329 is defined in the EAV attribute table
        $results = $read->fetchAll("$query");
        if (count($results) > 0)
            {
                return true; //there is an invoice
            }
        else
            {
                return false; //there is no invoice
            }
    }
    
    
    
                

             
             
    
     
}
