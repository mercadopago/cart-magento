 <?php
 
 
/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

class Mpexpress_Block_Checkout_Lightbox extends Mage_Core_Block_Abstract
{   
    
    protected function _toHtml(){
    

        $express = Mage::getModel('mpexpress/express');
       
        $preference = $express->getInitPoint();
        if (!isset($preference)):
    
        else:
        $html = '<div class="botao">';
        if($express->getConfigData('acc_origin') == 'MLB'):
        $html .= '<div class="left"/><h3 style="margin: 10px;">Continue pagando com MercadoPago</h3></div><div class="right" />';
        else:
        $html .= '<div class="left"/><h3 style="margin: 10px;">Continue pagando con MercadoPago</h3></div><div class="right" />';    
        endif;
        $html .= '<a href="' . $preference . '" name="MP-payButton" class="lightblue-ar-s-ov" mp-mode="modal" callback="execute_my_callback" id="btnPagar">Pagar</a>';
        $html .= '</div>';
        if($express->getConfigData('acc_origin') == 'MLB'):
        $html .= '<img src="' . $this->getSkinUrl('images/mpexpress/mercadopagobr.jpg') .'" alt="MercadoPago" title="MercadoPago" />';
        elseif($express->getConfigData('acc_origin') == 'MLM'):
        $html .= '<img src="' . $this->getSkinUrl('images/mpexpress/mercadopagomx.jpg') .'" alt="MercadoPago" title="MercadoPago" />';    
        elseif($express->getConfigData('acc_origin') == 'MLV'):
        $html .= '<img src="' . $this->getSkinUrl('images/mpexpress/mercadopagove.jpg') .'" alt="MercadoPago" title="MercadoPago" />';
        else:
        $html .= '<img src="' . $this->getSkinUrl('images/mpexpress/mercadopagoar.jpg') .'" alt="MercadoPago" title="MercadoPago" />';    
        endif;
        $html .= '  <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>';
        $html .= '  <script type="text/javascript">';
        $html .= '     function fireEvent(obj,evt){';
        $html .= '         var fireOnThis = obj;';
        $html .= '         if( document.createEvent ) {';
        $html .= '            var evObj = document.createEvent(\'MouseEvents\');';
        $html .= '            evObj.initEvent( evt, true, false );';
        $html .= '            fireOnThis.dispatchEvent( evObj );';
        $html .= '         } else if( document.createEventObject ) {';
        $html .= '            var evObj = document.createEventObject();';
        $html .= '            fireOnThis.fireEvent( \'on\' + evt, evObj );';
        $html .= '         }';
        $html .= '     }';
        $html .= '     fireEvent(document.getElementById("btnPagar"), \'click\')';
        $html .= '  </script>';
        $html .= '</div>';
        return utf8_decode($html);
        
        endif;
        
    }
}