<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

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

class MercadoPago_Standard_Model_Observer{
    
    private $banners_credit_card = array(
        "mla" => "http://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg",
        "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg",
        "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
        "mlm" => "http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG"
    );
    
    private $banner_ticket = array(
        "mla" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
        "mlb" => "http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/2014/230x60.png",
        "mco" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png",
        "mlm" => "https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png"
    );
    
    private $available_transparent_credit_cart = array('mla', 'mlb', 'mlm');
    private $available_transparent_ticket = array('mla', 'mlb', 'mlm');
    
    public function checkAndValidData($observer){
        $this->availableCheckout();
        
        $this->checkBanner('mercadopago_transparentticket', 'transparent');
        $this->checkBanner('mercadopago_transparent', 'transparent');
        $this->checkBanner('mercadopago_standard', 'checkout');
    }
    
    
    public function availableCheckout(){
        //verifica se o pais selecionado possui integra‹o para utilizar os checkouts transparents
        $core = new Mage_Core_Model_Resource_Setup('core_setup');
        $country = Mage::getStoreConfig('payment/mercadopago_configuration/country');
        
        if(!in_array($country, $this->available_transparent_credit_cart)){
            $core->setConfigData('payment/mercadopago_transparent/active', 0);
        }
        
        if(!in_array($country, $this->available_transparent_ticket)){
            $core->setConfigData('payment/mercadopago_transparentticket/active', 0);
        }
        
        
    }
    
    function checkBanner($model_path, $file){
        //pega o model/file
        $model = Mage::getModel($model_path . '/' . $file);
        
        //pega o banner do tipo de checkout
        $banner = $model->getConfigData('banner_checkout');
        
        //pega o pais configurado
        $country = Mage::getStoreConfig('payment/mercadopago_configuration/country');
        
        if($model_path == "mercadopago_transparentticket"){
            if($this->banner_ticket[$country] != $banner){
                $this->setNewBanner($model_path, $country);
            }    
        }else{
            if($this->banners_credit_card[$country] != $banner){
                $this->setNewBanner($model_path, $country);
            }
        }
        
        
    }
    public function setNewBanner($model, $country){
        //instacia model do core para atualiza os dados no banco de dados
        //no model n‹o existe fun‹o para fazer isso, por esse motivo foi feito assim
        $core = new Mage_Core_Model_Resource_Setup('core_setup');
        $core->setConfigData('payment/' . $model . '/banner_checkout', $this->getBannerByCountry($model, $country));
    }
    
    public function getBannerByCountry($model, $country){
        $banner = "";
        
        //caso seja boleto o banner  diferente
        if($model == "mercadopago_transparentticket"){
            $banner = $this->banner_ticket[$country];
        }else{
            $banner = $this->banners_credit_card[$country];
        }
        
        return $banner;
    }
    
}