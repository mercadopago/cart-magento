<?php
 
 /** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) | Edited: Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
 
 require_once(Mage::getBaseDir('lib') . '/mercadopago/mercadopago.php');
 
 class Mpexpress_Model_Checkout extends Mpexpress_Model_Mp {
  
  // do the client authentication
  public function __construct(){
   $standard = Mage::getModel('mpexpress/Express');
   $this->client_id = $standard->getConfigData('client_id');
   $this->client_secret = $standard->getConfigData('client_secret');
   $this->mp = new MP($this->client_id, $this->client_secret);
   $this->mp->sandbox_mode($standard->getConfigData('sandbox_checkout') == 1? true : false);
  }
  
  
  // Generate the botton
  public function GetCheckout($preference, $sandbox){

   $preferenceResult = $this->mp->create_preference($preference);
   
   if($this->mp->sandbox_mode()):
    return $preferenceResult["response"]["sandbox_init_point"];
   else:
    return $preferenceResult["response"]["init_point"];
   endif;
   
  }
  
  public function GetStatus($id){

   $paymentInfo = $this->mp->get_payment_info ($id);
   return $paymentInfo['response'];
  
  }
 }  
?>

