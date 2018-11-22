<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category       Payment Gateway
 * @package        MercadoPago
 * @author         Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 * @copyright      Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class MercadoPago_Core_Block_Customticket_Form
    extends MercadoPago_Core_Block_AbstractForm
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('mercadopago/custom_ticket/form.phtml');
    }

    public function getTicketsOptions()
    {
        $exclude_payment_methods = Mage::getStoreConfig('payment/mercadopago_customticket/excluded_payment_methods_ticket');
        $list_exclude = explode(",",$exclude_payment_methods);
      
        $paymentMethods = Mage::getModel('mercadopago/core')->getPaymentMethods();
        $tickets = array();

        foreach ($paymentMethods['response'] as $pm) {
            if ($pm['payment_type_id'] == 'ticket' || $pm['payment_type_id'] == 'atm') {
                
                //insert if not exist in list exclude payment method
                if(!in_array($pm['id'], $list_exclude)){
                  $tickets[] = $pm;                  
                }

            }
        }      
        return $tickets;
    }

    public function getCustomerInformation(){
      $customer = array();
      $key_address = Mage::getStoreConfig('payment/mercadopago_customticket/street_number_address');
      $key_address_number = Mage::getStoreConfig('payment/mercadopago_customticket/street_number_address_number');
      $use_tax_vat = Mage::getStoreConfig('payment/mercadopago_customticket/tax_vat');
      $customer['docnumber'] = "";

      $state_code = array(
        "485" => array("code" =>"AC", "state" => "Acre"),
        "486" => array("code" =>"AL", "state" => "Alagoas"),
        "487" => array("code" =>"AP", "state" => "Amapá"),
        "488" => array("code" =>"AM", "state" => "Amazonas"),
        "489" => array("code" =>"BA", "state" => "Bahia"),
        "490" => array("code" =>"CE", "state" => "Ceará"),
        "511" => array("code" =>"DF", "state" => "Distrito Federal"),
        "491" => array("code" =>"ES", "state" => "Espírito Santo"),
        "492" => array("code" =>"GO", "state" => "Goiás"),
        "493" => array("code" =>"MA", "state" => "Maranhão"),
        "494" => array("code" =>"MT", "state" => "Mato Grosso"),
        "495" => array("code" =>"MS", "state" => "Mato Grosso do Sul"),
        "496" => array("code" =>"MG", "state" => "Minas Gerais"),
        "497" => array("code" =>"PA", "state" => "Pará"),
        "498" => array("code" =>"PB", "state" => "Paraíba"),
        "499" => array("code" =>"PR", "state" => "Paraná"),
        "500" => array("code" =>"PE", "state" => "Pernambuco"),
        "501" => array("code" =>"PI", "state" => "Piauí"),
        "502" => array("code" =>"RJ", "state" => "Rio de Janeiro"),
        "503" => array("code" =>"RN", "state" => "Rio Grande do Norte"),
        "504" => array("code" =>"RS", "state" => "Rio Grande do Sul"),
        "505" => array("code" =>"RO", "state" => "Rondônia"),
        "506" => array("code" =>"RA", "state" => "Roraima"),
        "507" => array("code" =>"SC", "state" => "Santa Catarina"),
        "509" => array("code" =>"SE", "state" => "Sergipe"),
        "508" => array("code" =>"SP", "state" => "São Paulo"),
        "510" => array("code" =>"TO", "state" => "Tocantins")
      );

      if($use_tax_vat){
        $customer_session = Mage::getSingleton('customer/session')->getCustomer();
        $doc_number = $customer_session->getTaxvat();
        $customer['docnumber'] = $doc_number;
      }

      $address = array(
        "street_1" => Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getStreet(1),
        "street_2" => Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getStreet(2),
        "street_3" => Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getStreet(3),
        "street_4" => Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getStreet(4)
      );

      $data_customer = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getData();
      $customer['firstname'] = $data_customer['firstname'];
      $customer['lastname'] = $data_customer['lastname'];
      $customer['address'] = $address[$key_address];
      $customer['addressnumber'] = $address[$key_address_number];
      $customer['city'] = $data_customer['city'];
      $customer['state'] = $data_customer['region_id'];
      $customer['zipcode'] = $data_customer['postcode'];
      
      $state = "";
      if(isset($state_code[$customer['state']]) && isset($state_code[$customer['state']]['code'])){
        $state = $state_code[$customer['state']]['code'];
      }
      $customer['state_code'] = $state;

      return $customer;
    }
}
