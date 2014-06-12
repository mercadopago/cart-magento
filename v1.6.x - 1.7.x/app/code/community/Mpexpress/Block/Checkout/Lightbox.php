<?php
/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL).
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php *
 *  @category    Payment Gateway * @package    	MercadoPago
 *  @author      André Fuhrman (andrefuhrman@gmail.com) / Edited: Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @update-fix  Added try/catch to avoid a fatal error if the credentials are not set.
 * @author      Damian A. Pastorini (damian.pastorini@gmail.com)
 * @date        11-06-2014
 *
 */

class Mpexpress_Block_Checkout_Lightbox extends Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
        $express = Mage::getModel('mpexpress/express');
        try
        {
            $preference = $express->getInitPoint();
        }
        catch (Exception $e)
        {
            Mage::log($e->getMessage());
        }
        if (isset($preference))
        {
            $html = '<div class="botao">';
            if($express->getConfigData('acc_origin') == 'MLB'):
                $html .= '<div class="left"/><h3 style="margin: 10px;">Continue pagando com MercadoPago</h3></div><div class="right" />';
            else:
                $html .= '<div class="left"/><h3 style="margin: 10px;">Continue pagando con MercadoPago</h3></div><div class="right" />';
            endif;
            $html .= '<a href="' . $preference . '" name="MP-payButton" class="lightblue-ar-s-ov" mp-mode="modal" callback="execute_my_callback" id="btnPagar">Pagar</a>';
            $html .= '</div>';
            if($express->getConfigData('acc_origin') == 'MLB'):
                $html .= '<img src="http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg" alt="MercadoPago - Meios de pagamento" title="MercadoPago - Meios de pagamento" width="468" height="60"/>';
            elseif($express->getConfigData('acc_origin') == 'MLM'):
                $html .= '<img src="http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="468" height="60"/>';
            elseif($express->getConfigData('acc_origin') == 'MLV'):
                $html .= '<img src="http://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="468" height="60"/>';
            else:
                $html .= '<img src="http://imgmp.mlstatic.com/org-img/banners/ar/medios/468X60.jpg" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="468" height="60"/>" />';
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
        }
        else
        {
            $html = "An error occurred. Redirecting...<script>alert('".Mage::helper('checkout')->__('Error: su orden esta pendiente, por favor contacte al administrador.')."'); window.location.href = '".Mage::getUrl()."'; </script>";
        }
        return utf8_decode($html);
    }

}
