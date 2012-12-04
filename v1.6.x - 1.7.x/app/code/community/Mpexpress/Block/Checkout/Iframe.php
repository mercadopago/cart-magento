 <?php
 
/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

class Mpexpress_Block_Checkout_Iframe extends Mage_Core_Block_Abstract
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
       
      
        $html = '<center><iframe onreturn="checkoutReturn" id="MP-Checkout-IFrame" src="' . $preference . '" name="MP-Checkout" width="740" height="600" frameborder="0"></iframe></center>

        <script type="text/javascript">
	(function(){function $MPBR_load(){window.$MPBR_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;
	s.src = ("https:"==document.location.protocol?"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/":"http://mp-tools.mlstatic.com/buttons/")+"render.js";
	var x = document.getElementsByTagName("script")[0];x.parentNode.insertBefore(s, x);window.$MPBR_loaded = true;})();}
	window.$MPBR_loaded !== true ? (window.attachEvent ? window.attachEvent("onload", $MPBR_load) : window.addEventListener("load", $MPBR_load, false)) : null;})();
        </script>';
        
        return utf8_decode($html); 
        
        endif;
        
    }
}