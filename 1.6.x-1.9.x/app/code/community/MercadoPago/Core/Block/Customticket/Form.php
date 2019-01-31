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
    $show_form_missing_data = Mage::getStoreConfig('payment/mercadopago_customticket/show_form_missing_data');

    $data_customer = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getData();
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

    //if you are not registered with the user
    if($customer['docnumber'] == ""){
      $customer['docnumber'] = $data_customer['vat_id'];
    }

    $address = array(
      "street_1" => Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getStreet(1),
      "street_2" => Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getStreet(2),
      "street_3" => Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getStreet(3),
      "street_4" => Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getStreet(4)
    );


    $customer['firstname'] = $data_customer['firstname'];
    $customer['lastname'] = $data_customer['lastname'];
    $customer['address'] = $address[$key_address];
    $customer['addressnumber'] = $address[$key_address_number];
    $customer['city'] = $data_customer['city'];
    $customer['zipcode'] = $data_customer['postcode'];

    //check perfil person
    $customer['doctype'] = 'CPF';
    $documentNumber = preg_replace('/[.,\/-]/', '', $customer['docnumber']);
    if(strlen($documentNumber) > 11){
      $customer['doctype'] = 'CNPJ';
    }

    $customer['state'] =  $data_customer['region_id'];
    $state = "";
    if(empty($data_customer['region_id'])){
      $customer['state'] = $data_customer['region'];

      foreach($state_code as $regions => $codes){
        if($this->convertWithoutAccentAndLowerCase($codes['state']) == $this->convertWithoutAccentAndLowerCase($customer['state'])){
          $state = $codes['code'];
          break;
        }
      }
    }else{
      if(isset($state_code[$customer['state']]) && isset($state_code[$customer['state']]['code'])){
        $state = $state_code[$customer['state']]['code'];
      }
    }

    //set state_code
    $customer['state_code'] = $state;

    //default not display form
    $customer['show_form'] = false;
    if($show_form_missing_data){

      //set true, but check all data
      $customer['show_form'] = true;
      $mandatoryData = array("docnumber",
                             "firstname",
                             "lastname",
                             "address",
                             "addressnumber",
                             "city",
                             "zipcode",
                             "state",
                             "state_code");

      foreach($mandatoryData as $data){
        if(!isset($customer[$data]) || empty($customer[$data])){
          //set false if not exist or is empty
          $customer['show_form'] = false;
        }
      }
    }
    return $customer;
  }


  public function convertWithoutAccentAndLowerCase($string){
    // Remove all accents and convert all to lowercase
    return strtolower(
      preg_replace(
        array(
          "/(á|à|ã|â|ä)/",
          "/(Á|À|Ã|Â|Ä)/",
          "/(é|è|ê|ë)/",
          "/(É|È|Ê|Ë)/",
          "/(í|ì|î|ï)/",
          "/(Í|Ì|Î|Ï)/",
          "/(ó|ò|õ|ô|ö)/",
          "/(Ó|Ò|Õ|Ô|Ö)/",
          "/(ú|ù|û|ü)/",
          "/(Ú|Ù|Û|Ü)/"
          ,"/(ñ)/"
          ,"/(Ñ)/"
        ), 
        explode(
          " ",
          "a A e E i I o O u U n N"
        ), 
        $string
      )
    );
  }

}
