<?php
/**
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL).
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @category   	Payment Gateway
* @package    	MercadoPago
* @author      	Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
* @copyright  	Copyright (c) MercadoPago [http://www.mercadopago.com]
* @license    	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

require_once(Mage::getBaseDir('lib') . '/mercadopago/mercadopago.php');

class MercadoPago_Model_Observer{
    
    private $banners = array(
        "mercadopago_custom" => array(
            "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlm" => "http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG"    
        ),
        "mercadopago_customticket" => array(
            "mla" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/2014/230x60.png",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlm" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png"
        ),
        "mercadopago_standard" => array(
            "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
            "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
            "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
            "mlm" => "http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG",
            "mlc" => "https://secure.mlstatic.com/developers/site/cloud/banners/cl/468x60.gif"  
        )
    );
    
    private $available_transparent_credit_cart = array('mla', 'mlb', 'mlm');
    private $available_transparent_ticket = array('mla', 'mlb', 'mlm');
    
    public function checkAndValidData($observer){
        //verifica se o usuario é de teste ou nao
        $this->setSponsor();
        
        //verifica se o checkout esta disponível para o pais
        $this->availableCheckout();
        
        //verifica se os banners estao de acordo com o pais
        $this->checkBanner('mercadopago_custom');
        $this->checkBanner('mercadopago_customticket');
        $this->checkBanner('mercadopago_standard');
    }
    
    
    public function availableCheckout(){
        //verifica se o pais selecionado possui integracao para utilizar os checkouts transparents
        $core = new Mage_Core_Model_Resource_Setup('core_setup');
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        
        if(!in_array($country, $this->available_transparent_credit_cart)){
            $core->setConfigData('payment/mercadopago_custom/active', 0);
        }
        
        if(!in_array($country, $this->available_transparent_ticket)){
            $core->setConfigData('payment/mercadopago_customticket/active', 0);
        }   
    }
    
    function checkBanner($type_checkout){
        //get country    
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        $default_banner = $this->banners[$type_checkout][$country];
        
        //pega o banner do tipo de checkout
        $current_banner = Mage::getStoreConfig('payment/' . $type_checkout . '/banner_checkout');
        
        Mage::helper('mercadopago')->log("Type Checkout Path: " . $type_checkout, 'mercadopago.log');
        Mage::helper('mercadopago')->log("Current Banner: " . $current_banner, 'mercadopago.log');
        Mage::helper('mercadopago')->log("Default Banner: " . $default_banner, 'mercadopago.log');
        
        //verifico se o banner esta na lista de banner default
        //caso esteja verifico se esta de acordo com o pais
        if(in_array($current_banner, $this->banners[$type_checkout])){
            Mage::helper('mercadopago')->log("Banner default need update...", 'mercadopago.log');
            
            if($default_banner != $current_banner){
                //set o novo banner atualiza o banner
                $core = new Mage_Core_Model_Resource_Setup('core_setup');
                $core->setConfigData('payment/' . $type_checkout . '/banner_checkout', $default_banner);
                
                Mage::helper('mercadopago')->log('payment/' . $type_checkout . '/banner_checkout setted ' . $default_banner, 'mercadopago.log');
            }
        }
    }
    
    
    public function setSponsor(){
        Mage::helper('mercadopago')->log("Sponsor_id: " . Mage::getStoreConfig('payment/mercadopago/sponsor_id'), 'mercadopago.log');
        
        $sponsor_id = "";
        Mage::helper('mercadopago')->log("Valid user test", 'mercadopago.log');
        
        $client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
        Mage::helper('mercadopago')->log("Get client id: " . $client_id, 'mercadopago.log');
		
        $client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
        Mage::helper('mercadopago')->log("Get client secret: " . $client_secret, 'mercadopago.log');
        
		$mp = new MP($client_id, $client_secret);
        $user = $mp->get("/users/me");
        Mage::helper('mercadopago')->log("API Users response", 'mercadopago.log', $user);
        
            //caso api retorne 403 (error no get) verifica se a mensagem e do usuario com test credentials
        if( $user['status'] == 200 && !in_array("test_user", $user['response']['tags']) ){
            
            $sponsor_id = 1;
            $country = Mage::getStoreConfig('payment/mercadopago/country');
            
            switch($user['response']['site_id']){
                case 'MLA':
                    $sponsor_id = 186172525;
                    break;
                case 'MLB':
                    $sponsor_id = 186175129;
                    break;
                case 'MLM':
                    $sponsor_id = 186175064;
                    break;
                default:
                    $sponsor_id = "";
                    break;
            }
            
            Mage::helper('mercadopago')->log("Sponsor id setted", 'mercadopago.log', $sponsor_id);
        }
        
        $core = new Mage_Core_Model_Resource_Setup('core_setup');        
        $core->setConfigData('payment/mercadopago/sponsor_id', $sponsor_id);
        Mage::helper('mercadopago')->log("Sponsor saved", 'mercadopago.log', $sponsor_id);
    }
    
}