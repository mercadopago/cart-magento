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


class MercadoPago_Block_Success extends Mage_Core_Block_Template{
    
    protected function _construct(){

        parent::_construct();
        $this->setTemplate('mercadopago/success.phtml');
        
    }
    
    public function getOrder() {
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        return $order;
    }
    
    public function getPayment(){
        $order = $this->getOrder();
        $payment = $order->getPayment();
        return $payment;
    }
    
    public function getTotal(){
        $order = $this->getOrder();
        $total = $order->getBaseGrandTotal();
    
        if (!$total) {
            $total = $order->getBasePrice() + $order->getBaseShippingAmount();
        }
        
        $total = number_format($total, 2, '.', '');
        
        return $total;
    }
    
    public function getEntityId(){
        $order = $this->getOrder();
        
        return $order->getEntityId();
    }
    
    public function getPaymentMethod(){
        $payment_method = $this->getPayment()->getMethodInstance()->getCode();
        
        return $payment_method;
    }
    
    public function getInfoPayment(){
        $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $info_payments = Mage::getModel('mercadopago/core')->getInfoPaymentByOrder($order_id);
        
        return $info_payments;
    }
    
    public function getMessageByStatus($status, $status_detail, $payment_method, $amount, $installment){
        return Mage::getModel('mercadopago/core')->getMessageByStatus($status, $status_detail, $payment_method, $amount, $installment);
    }
}