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
        $list_exclude = explode(",", $exclude_payment_methods);

        $paymentMethods = Mage::getModel('mercadopago/core')->getPaymentMethods();
        $tickets = array();

        foreach ($paymentMethods['response'] as $pm) {
            if ($pm['payment_type_id'] == 'ticket' || $pm['payment_type_id'] == 'atm') {

                //insert if not exist in list exclude payment method
                if (!in_array($pm['id'], $list_exclude)) {
                    $tickets[] = $pm;
                }

            }
        }
        return $tickets;
    }

    public function getCustomerInformation()
    {
        $customer = array();
        $key_address = Mage::getStoreConfig('payment/mercadopago_customticket/street_number_address');
        $key_address_number = Mage::getStoreConfig('payment/mercadopago_customticket/street_number_address_number');
        $use_tax_vat = Mage::getStoreConfig('payment/mercadopago_customticket/tax_vat');
        $show_form_missing_data = Mage::getStoreConfig('payment/mercadopago_customticket/show_form_missing_data');

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $data_customer = $quote->getBillingAddress()->getData();
        $state_code = $this->getRegions();
        $customer['docnumber'] = "";
        
        if ($use_tax_vat) {
          $customer_session = Mage::getSingleton('customer/session')->getCustomer();

          $doc_number = $customer_session->getTaxvat();

          if(is_null($doc_number) && isset($data_customer['vat_id'])){
            $doc_number = $data_customer['vat_id'];
          }

          $customer['docnumber'] = $doc_number;
        }

        //if you are not registered with the user
        if ($customer['docnumber'] == "") {
            $customer['docnumber'] = $quote->getCustomerTaxvat();
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
        if (strlen($documentNumber) > 11) {
            $customer['doctype'] = 'CNPJ';
        }

        $customer['state'] = $data_customer['region_id'];
        $customer['state_code'] = $data_customer['region'];
        $state = "";

        if (empty($data_customer['region_id'])) {
            $customer['state'] = $data_customer['region'];
            $state = $this->_validateRegionId($state_code, $customer);
        } else {
            if (!empty($state_code[$customer['state']]) && isset($state_code[$customer['state']]['code'])) {
                $state = $data_customer['region_id'];
            }

            if(empty($state)){
                $state = $this->_validateRegionId($state_code, $customer);
            }
        }

        //set state_code
        $customer['state_code'] = $state;

        //default not display form
        $customer['show_form'] = false;
        if ($show_form_missing_data) {

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

            foreach ($mandatoryData as $data) {
                if (!isset($customer[$data]) || empty($customer[$data])) {
                    //set false if not exist or is empty
                    $customer['show_form'] = false;
                }
            }
        }
        return $customer;
    }

    /**
     * @param $state_code
     * @param $customer
     * @return null
     */
    private function _validateRegionId($state_code, $customer)
    {
        foreach ($state_code as $regions => $codes) {
            if ($this->convertWithoutAccentAndLowerCase($codes['state']) == $this->convertWithoutAccentAndLowerCase($customer['state']) || $this->convertWithoutAccentAndLowerCase($codes['code']) == $this->convertWithoutAccentAndLowerCase($customer['state_code'])) {
                return $regions;
            }
        }
        return null;
    }

    public function convertWithoutAccentAndLowerCase($string)
    {
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
                , "/(ñ)/"
                , "/(Ñ)/"
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
