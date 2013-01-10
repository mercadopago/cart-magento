<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */


class Mpexpress_Model_Mpcart extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {   

        $this->_init('mpexpress/mpcart');
    }
    
    public function generateEmptyOrder($id){

        $this->load($id);
        $orderid = $this->getOrderId();

       $acc_orign = Mage::getModel('mpexpress/express')->getConfigData('acc_origin');

       switch ($acc_orign):
            case 'MLB':
                $country = 'BR';
                break;
            case 'MLA':
                $country = 'AR';
                break;
            case 'MLM':
                $country = 'MX';
                break;
            case 'MLV':
                $country = 'ME';
                break;
        endswitch;
       
       
       if($orderid == null || $orderid == ''){
             
       $cartid = $this->getCartId();
       $cep =    $this->getPostalCode();
       $quote = Mage::getModel('sales/quote');;
       $quote->load($cartid);
       $quotedata = $quote->getData();

       
       $addressData = array(
                'firstname' => 'Waiting',
                'lastname' => '-',
                'street' => '-',
                'city' => '-',
                'telephone' => '-',
                'preference' => '-',
                'region_id' => '-', 
        );
        
        $email = $quote->getCustomerEmail();
        
        if ($email == '' || $email == null ){
        $email = '-';
        }

        $quote->setCustomerEmail($email);
        $bill = $quote->getBillingAddress();
        $bill->setShouldIgnoreValidation(true);
        $bill->setCity('-')
                ->setFirstname('Guess')
                ->setLastname('-')
                ->setStreet('-')
                ->setTelephone('-')
                ->setPreference('-')
                ->setCountryId($country)
                ->setPostcode($cep)
                ->setRegionId('0')
                ->setRegion('');
        $bill->save();
       

        $ship = $quote->getShippingAddress();
        $ship->setShouldIgnoreValidation(true);
        $ship->setCity('-')
                ->setFirstname('Guess')
                ->setLastname('-')
                ->setStreet('-')
                ->setTelephone('-')
                ->setPreference('-')
                ->setCountryId($country)
                ->setPostcode($cep)
                ->setRegionId('0')
                ->setRegion('')
                ->setCollectShippingRates(True)
                ->setPaymentMethod('mpexpress');
        $ship->save();
        $quote->setCartWasUpdated(true);
      
        $quote->getPayment()->importData(array('method' => 'mpexpress'));
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder()->getIncrementId();
        $this->setOrderId($order);
        $this->save();
      
       return $order;
       } else {
       return $orderid;
       }
        
    }
    
    
    
    
}



?>