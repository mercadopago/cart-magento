<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

class MercadoPago_Mpexpress_Model_Mysql4_Mpcart extends Mage_Core_Model_Mysql4_Abstract
{   

    
    protected function _construct()
    {
        $this->_init('mpexpress/mpcart', 'mpexpress_cart_id');
    }
    
  
    
 
}
?>
