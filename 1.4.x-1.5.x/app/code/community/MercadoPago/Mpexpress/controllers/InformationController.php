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
 * @author      Andr� Fuhrman (andrefuhrman@gmail.com)
 * @copyright  	Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license    	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class MercadoPago_Mpexpress_InformationController extends Mage_Core_Controller_Front_Action
{        
 
    protected $mp_id = null;
    protected $order_id = null;
    protected $_order = null;
    protected $_ship = null;
    protected $_email ;
    

    public function AddressAction() {
        
        parent::_construct();
        $this->loadLayout();
        
        $session = Mage::getSingleton('customer/session');
        $mpcart = Mage::getModel('mpexpress/mpcart');
        

        $params = $this->getRequest()->getParams();
        if(isset($params['hash'])){
        $mpcart->load($params['hash'],'hash'); 
        $this->mp_id = $mpcart->getMpexpressCartId();
        $session->setMpCartId($this->mp_id);    
        }
        
        $this->mp_id = $session->getMpCartId();

        if(!$this->mp_id){
            $this->_redirect('checkout/cart');
        }
        
        $mpcart = Mage::getModel('mpexpress/mpcart')->load($this->mp_id);
        $this->order_id = $mpcart->getOrderId();

        // If don´t have order, generate a order
        if ($this->order_id == null || $this->order_id == ''){
            $this->order_id = $mpcart->generateEmptyOrder($this->mp_id);
        } 

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
        $this->order_id  = $params['id'];
       
        
        if (empty($this->order_id)) {
           $mpcart = Mage::getSingleton('mpexpress/mpcart');  
           $this->mp_id = Mage::getSingleton('customer/session')->getMpCartId();
           $this->order_id = $mpcart->generateEmptyOrder($this->mp_id);          
        }
        
        Mage::getSingleton('customer/session')->setLastShip($ship);

        
  
        $name = $params['shipping']['firstname'];
        $lastname = $params['shipping']['lastname'];

        $addressData = $params['shipping'];
      
        if(!$this->_loadOrder($this->order_id)) {
	$this->getResponse()->setBody($this->__('error: missing order'));
	}
     

        $address_id = $this->_order['shipping_address_id'];
        $billing_id = $this->_order['billing_address_id'];


     

        $this->_ship = $params['shipping'];
        $this->addressSaveAction($address_id);
        $this->billingSaveAction($billing_id);
    
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
        $session    = Mage::getSingleton('customer/session'); 
        $address    = Mage::getModel('sales/order_address')->load($address_id);
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
    public function billingSaveAction($address_id)
    {
        $session    = Mage::getSingleton('customer/session'); 
        $address    = Mage::getModel('sales/order_address')->load($address_id);
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
