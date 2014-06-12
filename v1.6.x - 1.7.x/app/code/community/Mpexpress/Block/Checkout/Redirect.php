<?php
/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL).
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php *
 *  @category    Payment Gateway * @package    	MercadoPago
 *  @author      André Fuhrman (andrefuhrman@gmail.com)
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @update-fix  Added try/catch to avoid a fatal error if the credentials are not set.
 * @author      Damian A. Pastorini (damian.pastorini@gmail.com)
 * @date        11-06-2014
 *
 */

class Mpexpress_Block_Checkout_Redirect extends Mage_Core_Block_Abstract
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
            $html = '<meta http-equiv="REFRESH" content="0;url='. $preference .'">';
        }
        else
        {
            $html = "An error occurred. Redirecting...<script>alert('".Mage::helper('checkout')->__('Error: su orden esta pendiente, por favor contacte al administrador.')."'); window.location.href = '".Mage::getUrl()."'; </script>";
        }
        return utf8_decode($html);
    }

}
