<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */



// class MPexpress_Block_Template_Bt extends Mage_Core_Block_Template

class MPexpress_Block_Checkout_Cart extends Mage_Core_Block_Template
{
 
    
    protected $_email ;
     
    public function _construct()
    {
    
       
    }
    protected function _beforeToHtml()
    {
      
        // $this->createorder();
           $this->setMpCart();
    }
  
    protected function _toHtml()
    {
         return parent::_toHtml();
    }
    
    
    private function setMpCart(){
        
        $prod = Mage::getModel('catalog/product');
        $quote = Mage::getSingleton('checkout/session')->getQuote(); 
        $visible = $quote->getAllVisibleItems();
        $product_name = null;
        $count = 0;
        
        foreach ($visible as $item){
            if ($count > 0){
             $product_name .= ' + ' ;
            }
            $product_name .= $item->getName();
            $prod->loadByAttribute('sku', $item->getSku()); 
            $image[] = $prod->getImageUrl();
            $count ++;
        }
   
        
        
        
        $cartid = $quote->getEntityId();
        $tohash = $cartid.time();
        $hash = md5($tohash);
        $postalcode = $quote->getBillingAddress()->getPostcode();
  

        // save mp cart
        $mpcart = Mage::getModel('mpexpress/mpcart');
        $mpcart->setHash($hash); 
        $mpcart->setPostalCode($postalcode); 
        $mpcart->setCartId($cartid);
        $mpcart->save();
        $mpcart_id = $mpcart->getMpexpress_cart_id();
        
        $session = Mage::getSingleton('customer/session')->setMpCartId($mpcart_id);
        
        $item_price = $quote->getGrandTotal() + $quote->getShippingAddress()->getBaseShippingAmount();

        $item_price = number_format($item_price, 2, '.', '');
        
  
        $express = Mage::getModel('mpexpress/Express');      
        $excludes = $express->getConfigData('excluded_payment_methods');  
        
        $methods_excludes = preg_split("/[\s,]+/",$excludes); 
                 foreach ($methods_excludes as $exclude ){
                 $excluded_payment_methods[] = array('id' => $exclude);     
        }
        $baseUrl = Mage::getBaseUrl();
        $redirectURL = $baseUrl . 'mpexpress/information/address';
        
        if ($this->_email == '-'){
            $this->_email = '';
        }
        
        // setup mpCheckout
        
        $dados = array(
        'installments' => (int) $express->getConfigData('installments'),
        "external_reference" => 'magentoexpertcart-'.$hash ,// seu codigo de referencia, i.e. Numero do pedido da sua loja 
        "currency" => $express->getConfigData('currency'),// string Argentina: ARS (peso argentino) ó USD (Dólar estadounidense); Brasil: BRL (Real).
        "title" => $product_name,
        "description" => $product_name, // string
        'quantity' => (int) 1,// int 
        'image' => $image[0],  // Imagem, string
        'amount' => $item_price, //decimal
        'payment_firstname' => '',// string
        'payment_lastname' => '',// string
        'email' => $this->_email,// string
        'pending' => $redirectURL, // string 
        'approved' => $redirectURL, // string
        );
    
       
         $checkout = Mage::getModel('mpexpress/Checkout');  

         $botton = $checkout->GetCheckout($dados,$excludes);
         $this->setPreference($botton); 
        
        
        // continuar daqui
        
    }
    
       // to delet
    private function createorder(){
        
       $quote = Mage::getSingleton('checkout/session')->getQuote();
      
        // set customer no email
        $this->_email = $quote->getCustomerEmail();
        
        if ($this->_email == '' || $this->_email == null ){
        $this->_email = '-';
         }
        
        $quote->setCustomerEmail($this->_email);
       
        
        $addressData = array(
                'firstname' => 'Guess',
                'lastname' => '-',
                'street' => '-',
         //       'city' => '-',
                'telephone' => '-',
                'preference' => '-',
                'region_id' => '-', // id from directory_country_region table
        );
  
        
        $quote->getPayment()->importData(array('method' => 'mpexpress'));

        $quote->collectTotals()->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();
    
        $baseUrl = Mage::getBaseUrl();
        $botao = $this->Checkout($order);
        $this->setPreference($botao);
     
    }
    
    // to delet
    private function Checkout($order){
     
        $orderId = $order->getIncrementId();
        $name = '#' . $orderId . ' - '; 
        $model = Mage::getModel('catalog/product');
        
        foreach ($order->getAllVisibleItems() as $item) {
            $name .= $item->getName();
            $prod = $model->loadByAttribute('sku', $item->getSku()); 
            $image[] = $prod->getImageUrl();
         
        }
        
        $item_price = $order->getBaseGrandTotal();
        if (!$item_price) {
            $item_price = $order->getBasePrice() + $order->getBaseShippingAmount();
        }
        $session = Mage::getSingleton('customer/session')->setOrderId($orderId);
        $item_price = number_format($item_price, 2, '.', '');

        $express = Mage::getModel('mpexpress/Express');      
        $excludes = $express->getConfigData('excluded_payment_methods');  
        
        $methods_excludes = preg_split("/[\s,]+/",$excludes); 
                 foreach ($methods_excludes as $exclude ){
                 $excluded_payment_methods[] = array('id' => $exclude);     
        }
        $baseUrl = Mage::getBaseUrl();
        $redirectURL = $baseUrl . 'mpexpress/information/address';
        
        if ($this->_email == '-'){
            $this->_email = '';
        }
        
        $dados = array(
        'installments' => $express->getConfigData('installments'),
        "external_reference" => 'mpexpresscart-'.$orderId ,// seu codigo de referencia, i.e. Numero do pedido da sua loja 
        "currency" => $express->getConfigData('currency'),// string Argentina: ARS (peso argentino) ó USD (Dólar estadounidense); Brasil: BRL (Real).
        "title" => $name,
        "description" => $name, // string
        'quantity' => (int) 1,// int 
        'image' => $image[0],  // Imagem, string
        'amount' => $item_price, //decimal
        'payment_firstname' => '',// string
        'payment_lastname' => '',// string
        'email' => $this->_email,// string
        'pending' => $redirectURL, // string 
        'approved' => $redirectURL, // string
        );
    
       
     $checkout = Mage::getModel('mpexpress/Checkout');  
     
     $botton = $checkout->GetCheckout($dados,$excludes);
     return $botton;
        
    }
    
   

}

?>