 <?php
 
/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

class MercadoPago_Mpexpress_Block_Checkout_Iframe extends Mage_Core_Block_Abstract
{   
    
    protected function _toHtml(){
    
       $express = Mage::getModel('mpexpress/express');
       
        $preference = $express->getInitPoint();
        if (!isset($preference)):
            
            
        endif;
        $express = Mage::getModel('mpexpress/express');
       
        $preference = $express->getInitPoint();
        if (!isset($preference)):
    
        else:
       
       $html = '<center><iframe id="MP-Checkout-IFrame" frameborder="0" style="width: 740px; height: 480px;" src="' . $preference . '"></center>';   
        return utf8_decode($html); 
        
        endif;
        
    }
}