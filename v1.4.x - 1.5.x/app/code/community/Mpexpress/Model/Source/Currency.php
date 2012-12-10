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
 * @author      Carlos Corrêa (cadu.rcorrea@gmail.com)
 * @copyright  	Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license    	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mpexpress_Model_Source_Currency
{
	//	public function toOptionArray ()
//	{
//        return array(
//            array('value' => 'ARS', 'label'=>Mage::helper('adminhtml')->__('Pesos Argentinos')),
//            array('value' => 'BRL', 'label'=>Mage::helper('adminhtml')->__('Reais')),
//            array('value' => 'USD', 'label'=>Mage::helper('adminhtml')->__('Dolares')),
//        );
//	}
        
        
        public function toOptionArray ()
	{
    
       $standard = Mage::getModel('mpexpress/Express');
       //$standard = new Mpexpress_Model_Express();
  
       $site = $standard->getConfigData('acc_origin');
       $mp = Mage::getModel('mpexpress/Mp');
       if ( $site != "" ) {
      
        $url = "https://api.mercadolibre.com/sites/$site";
        $return_code = 200;
        $options = array();
        $header = array();
        $response = $mp->DoPost($options,$url,$header,$return_code,"data","GET");
        
        

        foreach($response['currencies'] as $v){
             $cur[] = array('value' => $v['id'], 'label'=>Mage::helper('adminhtml')->__($v['id']));
        }   
        } else {
        $cur[] = array('value' => "", 'label'=>Mage::helper('adminhtml')->__("Please Reload Page"));
        }

        return $cur;
	}
}
###