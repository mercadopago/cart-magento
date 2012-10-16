<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

class Mpexpress_Model_Checkout extends Mpexpress_Model_Mp {
    
      
       // do the client authentication
    public function __construct(){
     $standard = Mage::getModel('mpexpress/Express');
     $this->client_id = $standard->getConfigData('client_id');
     $this->client_secret = $standard->getConfigData('client_secret');
    }
       

      // Generate the botton
      public function GetCheckout($data,$excludes){
                
                    
               if($data['installments']  == 0 || $data['installments']  == ''){
                    $data['installments'] = null;
                }

          
          
            if($excludes != ''){
                
                 $methods_excludes = preg_split("/[\s,]+/", $excludes); 
                 foreach ($methods_excludes as $exclude ){
                 $excludemethods[] = array('id' => $exclude);     
                 }
         
              
                $opt = array(
                "external_reference" => $data['external_reference'],
                "items" => array(
                    array ("id" => 'meu_cliente_id', // updated
                    "title" => $data['title'],
                    "description" => $data['quantity'] . ' x ' . $data['title'],
                    "quantity" => 1,
                    "unit_price" => round($data['amount'], 2),
                    "currency_id" => $data['currency'],
                    "picture_url"=> $data['image'],
                    )),
                    "payer" => array(
                     "name" => $data['payment_firstname'],
                     "surname" => $data['payment_lastname'],
                     "email" => $data['email']
                    ),
                   "back_urls" => array(
                   "pending" => $data['pending'],
                   "success" => $data['approved']
                   ),           
                   "payment_methods" => array(
                   "excluded_payment_methods" => $excludemethods,
                   "installments" => $data['installments']      
                   )
                );
            }else{
                $opt = array(
                "external_reference" => $data['external_reference'],
                "items" => array(
                    array ("id" => $data['external_reference'], // updated
                    "title" => $data['title'],
                    "description" => $data['quantity'] . ' x ' . $data['title'],
                    "quantity" => 1,
                    "unit_price" => round($data['amount'], 2),
                    "currency_id" => $data['currency'],
                    "picture_url"=> $data['image'],
                    )),
                    "payer" => array(
                     "name" => $data['payment_firstname'],
                     "surname" => $data['payment_lastname'],
                     "email" => $data['email']
                    ),
                   "back_urls" => array(
                   "pending" => $data['pending'],
                   "success" => $data['approved']
                   ),
                   "payment_methods" => array(
                   "installments" => $data['installments']      
                   )  
                );
                
            }

            $this->getAccessToken(); 
            $url = 'https://api.mercadolibre.com/checkout/preferences?access_token=' . $this->accesstoken;
            $header = array('Content-Type:application/json','Accept: application/json');
            $dados = $this->DoPost($opt,$url,$header,'201','json','post');
            $link = $dados['init_point'];
            $bt = '<a href="'.$link.'" name="MP-payButton" class="blue-l-rn-ar">Comprar</a>
            <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>';
            return $link;
            
      }
      
      public function GetStatus($id){
     
            $this->getAccessToken(); 
            $url = "https://api.mercadolibre.com/collections/notifications/" . $id . "?access_token=" . $this->accesstoken;
            $header = array('Accept: application/json', 'Content-Type: application/x-www-form-urlencoded');
            $retorno = $this->DoPost($opt=null,$url,$header,'200','none','post');
            return $retorno;
                   
      }
      

}  
?>

