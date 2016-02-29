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
class MercadoPago_Core_Helper_Data
    extends Mage_Payment_Helper_Data
{

    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';
    const XML_PATH_PUBLIC_KEY = 'payment/mercadopago_custom_checkout/public_key';
    const XML_PATH_CLIENT_ID = 'payment/mercadopago_standard/client_id';
    const XML_PATH_CLIENT_SECRET = 'payment/mercadopago_standard/client_secret';

    const PLATFORM_OPENPLATFORM = 'openplatform';
    const PLATFORM_STD = 'std';
    const TYPE = 'magento';

    public function log($message, $file = "mercadopago.log", $array = null)
    {
        //pega a configuração de log no admin, essa variavel vem como true por padrão
        $action_log = Mage::getStoreConfig('payment/mercadopago/logs');

        //caso tenha um array, transforma em json para melhor visualização
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        //set log
        Mage::log($message, null, $file, $action_log);
    }

    public function getApiInstance()
    {
        $params = func_num_args();
        if ($params > 2 || $params < 1) {
            Mage::throwException("Invalid arguments. Use CLIENT_ID and CLIENT SECRET, or ACCESS_TOKEN");
        }
        if ($params == 1) {
            $api = new MercadoPago_Lib_Api(func_get_arg(0));
            $api->set_platform(self::PLATFORM_OPENPLATFORM);
        } else {
            $api = new MercadoPago_Lib_Api(func_get_arg(0), func_get_arg(1));
            $api->set_platform(self::PLATFORM_STD);
        }
        if (Mage::getStoreConfigFlag('payment/mercadopago_standard/sandbox_mode')) {
            $api->sandbox_mode(true);
        }

        $api->set_type(self::TYPE);
        $api->set_so((string) Mage::getConfig()->getModuleConfig("MercadoPago_Core")->version);

        return $api;

    }

    public function isValidAccessToken($accessToken)
    {
        $mp = Mage::helper('mercadopago')->getApiInstance($accessToken);
        $response = $mp->get("/v1/payment_methods");
        if ($response['status'] == 401 || $response['status'] == 400) {
            return false;
        }

        return true;
    }

    public function isValidClientCredentials($clientId, $clientSecret)
    {
        $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
        try {
            $mp->get_access_token();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function getAccessToken()
    {
        $clientId = Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(self::XML_PATH_CLIENT_SECRET);
        return $this->getApiInstance($clientId, $clientSecret)->get_access_token();
    }

    public function getStatusOrder($status)
    {
        switch ($status) {
            case 'approved': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_approved');
                break;
            }
            case 'refunded': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_refunded');
                break;
            }
            case 'in_mediation': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_mediation');
                break;
            }
            case 'cancelled': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_cancelled');
                break;
            }
            case 'rejected': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_rejected');
                break;
            }
            case 'chargeback': {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_chargeback');
                break;
            }
            default: {
                $status = Mage::getStoreConfig('payment/mercadopago/order_status_in_process');
            }
        }

        return $status;
    }

    /**
     * Get the assigned state of an order status
     *
     * @param string $status
     */
    public function _getAssignedState($status)
    {
        $item = Mage::getResourceModel('sales/order_status_collection')
            ->joinStates()
            ->addFieldToFilter('main_table.status', $status);

        return array_pop($item->getItems())->getState();
    }

    public function getMessage($status, $payment)
    {
        $rawMessage = Mage::helper('mercadopago')->__(Mage::helper('mercadopago/statusOrderMessage')->getMessage($status));
        $rawMessage .= Mage::helper('mercadopago')->__('<br/> Payment id: %s', $payment['id']);
        $rawMessage .= Mage::helper('mercadopago')->__('<br/> Status: %s', $payment['status']);
        $rawMessage .= Mage::helper('mercadopago')->__('<br/> Status Detail: %s', $payment['status_detail']);

        return $rawMessage;
    }

    public function setOrderSubtotals($data, $order)
    {
        if (isset($data['total_paid_amount'])){
            $balance = $this->_getMultiCardValue($data['total_paid_amount']);
        } else {
            $balance = $data['transaction_details']['total_paid_amount'];
        }
        $shippingCost = $this->_getMultiCardValue($data['shipping_cost']);

        $order->setGrandTotal($balance);
        $order->setBaseGrandTotal($balance);
        $order->setBaseShippingAmount($shippingCost);
        $order->setShippingAmount($shippingCost);

        $couponAmount = $this->_getMultiCardValue($data['coupon_amount']);
        $transactionAmount = $this->_getMultiCardValue($data['transaction_amount']);

        if ($couponAmount) {
            $order->setDiscountCouponAmount($couponAmount * -1);
            $order->setBaseDiscountCouponAmount($couponAmount * -1);
            $balance = $balance - ($transactionAmount - $couponAmount + $shippingCost);
        } else {
            $balance = $balance - $transactionAmount - $shippingCost;
        }

        if (round($balance,4) > 0) {
            $order->setFinanceCostAmount($balance);
            $order->setBaseFinanceCostAmount($balance);
        }
    }

    /**
     * @param $payment
     *
     * @return mixed
     */
    public function setPayerInfo(&$payment)
    {
        $payment["trunc_card"] = "xxxx xxxx xxxx " . $payment['card']["last_four_digits"];
        $payment["cardholder_name"] = $payment['card']["cardholder"]["name"];
        $payment['payer_first_name'] = $payment['payer']['first_name'];
        $payment['payer_last_name'] = $payment['payer']['last_name'];
        $payment['payer_email'] = $payment['payer']['email'];

        return $payment;
    }

    protected function _getMultiCardValue($fullValue) {
        $finalValue = 0;
        $values = explode('|', $fullValue);
        foreach ($values as $value) {
            $value = (float) str_replace(' ', '', $value);
            $finalValue = $finalValue + $value;
        }

        return $finalValue;
    }

}
