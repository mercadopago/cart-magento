<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */



// class MPexpress_Block_Template_Bt extends Mage_Core_Block_Template

class MPexpress_Block_Botao extends Mage_Core_Block_Template{
    protected $_shouldRender = true;
    protected $_checkoutpage = 'mpexpress/checkout';
    protected $_addcart = 'mpexpress/checkout/addcart';

     
    public function _construct(){
        Mage::log('MPexpress_Block_Botao');   
       
    }
    protected function _beforeToHtml(){ 
       $this->setcheckoutexpress($this->getUrl($this->_checkoutpage))->setMpAddCart('mpexpress/checkout/addcart')
        ->setimgcheckout($this->getSkinUrl('images/mercadopago/pagar.jpg'));  
    }
  
    protected function _toHtml(){
         return parent::_toHtml();
    }
    
   

}

?>