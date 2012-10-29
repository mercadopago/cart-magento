<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

class Mpexpress_InformationController extends Mage_Core_Controller_Front_Action
{        
    
    
    protected $_order = null;
    protected $_id = null;
    protected $_ship = null;
    
    
    public function AddressAction() {
        
        parent::_construct();
        $this->loadLayout();
       
        
        
        $id = Mage::getSingleton('customer/session')->getOrderId();
        if(!$id){
            $this->_redirect('checkout/cart');
        }
        $order = Mage::getModel('sales/order')->loadByIncrementId($id); 
   
        $block = $this->getLayout()->createBlock('mpexpress/information_form')->setTemplate('mpexpress/information/form.phtml');
              
        $this->getLayout()->getBlock('content')->append($block);
  
        $root = $this->getLayout()->getBlock('root');
     
        $template = "page/1column.phtml";
        
        $root->setTemplate($template);
         
        $this->renderLayout(); 
       
    }
    
    public function PostAction() {
        
        parent::_construct();
        $this->loadLayout();
        $params = $this->getRequest()->getParams();
        $ship = $params['shipping'];
        if ( empty ($this->_order)) {
            $id = Mage::getSingleton('customer/session')->getOrderId();
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($id); 
            
        }
        
        Mage::getSingleton('customer/session')->setLastShip($ship);

        
  
        $name = $params['shipping']['firstname'];
        $lastname = $params['shipping']['lastname'];

        $addressData = $params['shipping'];
      
        if(!$this->_loadOrder($id)) {
	$this->getResponse()->setBody($this->__('error: missing order'));
	}
     
      
               
 
        $address_id = $this->_order['shipping_address_id'];
        $billing_id = $this->_order['billing_address_id'];
        $this->_ship = $params['shipping'];
        $this->addressSaveAction($address_id);
        $this->addressSaveAction($billing_id);
    

//        foreach ($ship as $field => $value){
//           echo $field . (' - ') ;
//        $this->_editAddress(1,$field,$value);
//     
//        }
//        

         
//         
//        $address = $this->_order->getShippingAddress();
//	$addressSet = 'setShippingAddress';
//    //    var_dump($address);die;              
//        $this->_order->$addressSet($addressData);
//        $this->_order->save();
//        
//        var_dump($this->_order->getShippingAddress());
    }
    
         public function SuccessAction() {
             
        parent::_construct();
        $this->loadLayout();
            
        $block = $this->getLayout()->createBlock('mpexpress/information_success')->setTemplate('mpexpress/information/success.phtml');
              
        $this->getLayout()->getBlock('content')->append($block);
  
        $root = $this->getLayout()->getBlock('root');
     
        $template = "page/1column.phtml";
        
        $root->setTemplate($template);

        $this->renderLayout(); 
             
         }
         
         public function ErrorAction() {
             
             echo 'Error Page';
             
         }
        
    
         private function _loadOrder($id) {
	$this->_order = Mage::getModel('sales/order')->loadByIncrementId($id); 
	if(!$this->_order->getId()) return false;
	return true;
	}
        
             
        
        public function addressSaveAction($address_id)
    {
        $session = Mage::getSingleton('customer/session'); 
        $address    = Mage::getModel('sales/order_address')->load($address_id);
        $data       = $this->getRequest()->getPost();
        $data       = $this->_ship;
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $address->implodeStreetAddress()
                    ->save();
             //   $this->_getSession()->addSuccess(Mage::helper('sales')->__('The order address has been updated.'));
                  $this->_redirect('mpexpress/information/success/');
                return;
            } catch (Mage_Core_Exception $e) {
                 $session->addError($e->getMessage());
            } catch (Exception $e) {
                  $session = Mage::getSingleton('customer/session'); 
                  $session->addException(
                  $e,
                  Mage::helper('sales')->__('An error occurred while updating the order address. The address has not been changed.')         
                );
               $session->addSuccess(Mage::helper('sales')->__('An error occurred while updating the order address.'));   
               $this->_redirect('mpexpress/information/address');
            }
              $this->_redirect('mpexpress/information/address');
        } else {
             $this->_redirect('mpexpress/information/address');
        }
    }

    
      
    
 
    
  
     
  
  

}


?>
