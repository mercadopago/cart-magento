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

class MercadoPago_Core_Model_Standard_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_formBlockType = 'mercadopago/standard_form';
    protected $_infoBlockType = 'mercadopago/standard_info';

    protected $_code = 'mercadopago_standard';
    
    protected $_isGateway                   = true;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canFetchTransactionInfo     = true;
    protected $_canCreateBillingAgreement   = true;
    protected $_canReviewPayment            = true;

    public function postPago()
    {
        //seta sdk php mercadopago
        $client_id = Mage::getStoreConfig('payment/mercadopago/client_id');
        $client_secret = Mage::getStoreConfig('payment/mercadopago/client_secret');
        $mp = Mage::helper('mercadopago')->getApiInstance($client_id, $client_secret);

        //monta a prefernecia
        $pref = $this->makePreference();
        Mage::helper('mercadopago')->log("make array", 'mercadopago-standard.log', $pref);
        
        //faz o posto do pagamento
        $response = $mp->create_preference($pref);
        Mage::helper('mercadopago')->log("create preference result", 'mercadopago-standard.log', $response);
        
        $array_assign = array();
        
        if ($response['status'] == 200 || $response['status'] == 201){
            $payment = $response['response'];
            if (Mage::getStoreConfigFlag('payment/mercadopago/sandbox_mode')) {
                $init_point = $payment['sandbox_init_point'];
            } else {
                $init_point = $payment['init_point'];
            }

            $array_assign = array(
                "init_point"      => $init_point,
                "type_checkout"   => $this->getConfigData('type_checkout'),
                "iframe_width"    => $this->getConfigData('iframe_width'),
                "iframe_height"   => $this->getConfigData('iframe_height'),
                "banner_checkout" => $this->getConfigData('banner_checkout'),
                "status"          => 201
            );

            Mage::helper('mercadopago')->log("Array preference ok", 'mercadopago-standard.log');
        } else {
            $array_assign = array(
                "message" => Mage::helper('mercadopago')->__('An error has occurred. Please refresh the page.'),
                "json"    => json_encode($response),
                "status"  => 400
            );

            Mage::helper('mercadopago')->log("Array preference error", 'mercadopago-standard.log');
        }

        return $array_assign;
    }

    public function getOrderPlaceRedirectUrl()
    {
        // requisicao vem da pagina de finalizacao de pedido
        return Mage::getUrl('mercadopago/pay', array('_secure' => true));
    }

    public function getDiscount($order)
    {
        $discount = 0;
        $order = $order->getData();

        if (isset($order['base_discount_amount']) && $order['base_discount_amount'] < 0) {
            $discount = $order['base_discount_amount'];
        }

        return $discount;
    }

    protected function getItems($order) {
        $items = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            //get image
            try {
                $image = $product->getImageUrl();
            } catch (Exception $e) {
                $image = "";
            }

            $items[] = array(
                "id"          => $item->getSku(),
                "title"       => $product->getName(),
                "description" => $product->getName(),
                "picture_url" => $image,
                "category_id" => Mage::getStoreConfig('payment/mercadopago/category_id'),
                "quantity"    => (int)number_format($item->getQtyOrdered(), 0, '.', ''),
                "unit_price"  => (float)number_format($product->getFinalPrice(), 2, '.', '')
            );
        }

        return $items;
    }

    protected function getTotalItems($items) {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['unit_price'];
        }
        return $total;
    }

    protected function getExcludedPaymentsMethods($checkout) {
        $excludedMethods = array();
        $excluded_payment_methods = $checkout->getConfigData('excluded_payment_methods');
        $arr_epm = explode(",", $excluded_payment_methods);
        if (count($arr_epm) > 0) {
            foreach ($arr_epm as $m) {
                $excludedMethods[] = array("id" => $m);
            }
        }
    }

    public function makePreference()
    {
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $payment = $order->getPayment();

        $arr = array();

        $arr['external_reference'] = $orderIncrementId;

        $arr['items'] = $this->getItems();
        $total_item = $this->getTotalItems($arr['items']);

        $order_amount = (float)$order->getBaseGrandTotal();
        if (!$order_amount) {
            $order_amount = (float)$order->getBasePrice() + $order->getBaseShippingAmount();
        }

        $total_item += (float)$order->getBaseShippingAmount();

        if ($total_item > $order_amount || $total_item < $order_amount) {
            $diff_price = $order_amount - $total_item;

            $arr['items'][] = array(
                "title"       => "Difference amount of the items with a total",
                "description" => "Difference amount of the items with a total",
                "category_id" => Mage::getStoreConfig('payment/mercadopago/category_id'),
                "quantity"    => 1,
                "unit_price"  => (float)$diff_price
            );

            Mage::helper('mercadopago')->log("Total itens: " . $total_item, 'mercadopago-standard.log');
            Mage::helper('mercadopago')->log("Total order: " . $order_amount, 'mercadopago-standard.log');
            Mage::helper('mercadopago')->log("Difference add itens: " . $diff_price, 'mercadopago-standard.log');
        }

        $shipping = $order->getShippingAddress()->getData();
        $arr['shipments']['receiver_address'] = array(
            "floor"         => "-",
            "zip_code"      => $shipping['postcode'],
            "street_name"   => $shipping['street'] . " - " . $shipping['city'] . " - " . $shipping['country_id'],
            "apartment"     => "-",
            "street_number" => "0"
        );
        $arr['payer']['phone'] = array(
            "area_code" => "-",
            "number"    => $shipping['telephone']
        );

        //adiciona o valor do frete nas preferencias
        $shippingCost = $order->getBaseShippingAmount();
        if (!empty($shippingCost)) {
            $arr['shipments']['cost'] = (float)$order->getBaseShippingAmount();
        }

        $billing_address = $order->getBillingAddress();
        $billing_address = $billing_address->getData();

        $arr['payer']['date_created'] = date('Y-m-d', $customer->getCreatedAtTimestamp()) . "T" . date('H:i:s', $customer->getCreatedAtTimestamp());
        $arr['payer']['email'] = htmlentities($customer->getEmail());
        $arr['payer']['first_name'] = htmlentities($customer->getFirstname());
        $arr['payer']['last_name'] = htmlentities($customer->getLastname());

        if (isset($payment['additional_information']['doc_number']) && $payment['additional_information']['doc_number'] != "") {
            $arr['payer']['identification'] = array(
                "type"   => "CPF",
                "number" => $payment['additional_information']['doc_number']
            );
        }

        $arr['payer']['address'] = array(
            "zip_code"      => $billing_address['postcode'],
            "street_name"   => $billing_address['street'] . " - " . $billing_address['city'] . " - " . $billing_address['country_id'],
            "street_number" => "0"
        );

        $arr['back_urls'] = array(
            "success" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true) . "mercadopago/success",
            "pending" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true) . "mercadopago/success",
            "failure" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true) . "mercadopago/success"
        );

        $arr['notification_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true) . "mercadopago/notifications?checkout=standard";

        $checkout = Mage::getModel('mercadopago/standard_payment');
        $arr['payment_methods']['excluded_payment_methods'] = $this->getExcludedPaymentsMethods($checkout);
        $installments = $checkout->getConfigData('installments');
        $arr['payment_methods']['installments'] = (int)$installments;


        $auto_return = $checkout->getConfigData('auto_return');
        if ($auto_return == 1) {
            $arr['auto_return'] = "approved";
        }

        $sponsor_id = Mage::getStoreConfig('payment/mercadopago/sponsor_id');
        Mage::helper('mercadopago')->log("Sponsor_id identificado", 'mercadopago-standard.log', $sponsor_id);
        $arr['sponsor_id'] = (int)$sponsor_id;
        return $arr;
    }

    public function getSuccessBlockType()
    {
        return $this->_successBlockType;
    }
}
