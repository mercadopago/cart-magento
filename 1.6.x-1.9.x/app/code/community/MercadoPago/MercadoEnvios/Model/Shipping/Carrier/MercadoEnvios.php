<?php

class MercadoPago_MercadoEnvios_Model_Shipping_Carrier_MercadoEnvios
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'mercadoenvios';
    const INVALID_METHOD = -1;

    protected $_code = self::CODE;
    protected $_available;
    protected $_methods;
    protected $_request;


    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->isActive()) {
            return false;
        }
        $this->_request = $request;

        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');
        foreach (array_keys($this->getAllowedMethods()) as $methodId) {
            $rate = $this->_getRate($methodId);
            $result->append($rate);
        }

        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = $this->getDataAllowedMethods();
        $allowedMethods = array();
        if (is_array($methods)) {
            foreach ($methods as $method) {
                if (isset($method['shipping_method_id'])) {
                    if ($this->_isAvailableRate($method['shipping_method_id'])) {
                        $allowedMethods[$method['shipping_method_id']] = $method['name'];
                    }
                }
            }
        } else {
            $allowedMethods[self::INVALID_METHOD] = $methods;
        }

        return $allowedMethods;
    }

    protected function getDataAllowedMethods()
    {
        if (empty($this->_methods) && !empty($this->_request)) {
            $quote = Mage::helper('mercadopago_mercadoenvios')->getQuote();

            $shippingAddress = $quote->getShippingAddress();
            if (empty($shippingAddress)) {
                return null;
            }
            $postcode = $shippingAddress->getPostcode();

            try {
                $helperMe = Mage::helper('mercadopago_mercadoenvios');
                $dimensions = $helperMe->getDimensions($helperMe->getAllItems($this->_request->getAllItems()));
            } catch (Exception $e) {
                $this->_methods = self::INVALID_METHOD;

                return;
            }

            $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
            $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
            $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);

            $params = array(
                "dimensions" => $dimensions,
                "zip_code"   => $postcode,
            );

            $freeMethod = Mage::helper('mercadopago_mercadoenvios')->getFreeMethod($this->_request);
            if (!empty($freeMethod)) {
                $params['free_method'] = $freeMethod;
            }
            $response = $mp->get("/shipping_options", $params);
            if ($response['status'] == 200) {
                $this->_methods = $response['response']['options'];
            } else {
                $this->_methods = self::INVALID_METHOD;
                if (isset($response['response']['message'])) {
                    Mage::register('mercadoenvios_msg', $response['response']['message']);
                }
                Mage::helper('mercadopago_mercadoenvios')->log('Request params: ', $params);
                Mage::helper('mercadopago_mercadoenvios')->log('Error response API: ', $response);
            }
        }

        return $this->_methods;
    }

    public function getDataMethod($methodId)
    {
        $methods = $this->getDataAllowedMethods();
        if (!empty($methods)) {
            foreach ($methods as $method) {
                if ($method['shipping_method_id'] == $methodId) {
                    return new Varien_Object($method);
                }
            }
        }

        return new Varien_Object();
    }

    protected function _getRate($methodId)
    {
        if ($methodId == self::INVALID_METHOD) {
            return $this->_getErrorRate();
        }
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $dataMethod = $this->getDataMethod($methodId);
        $rate->setCarrier($this->_code);

        $estimatedDate = $this->_getEstimatedDate($dataMethod->getEstimatedDeliveryTime());
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod($methodId);
        $rate->setMethodTitle($dataMethod->getName() . ' ' . Mage::helper('mercadopago')->__('(estimated date %s)', $estimatedDate));
        if (!empty($this->_request) && $this->_request->getFreeShipping()) {
            $rate->setPrice(0.00);
        } else {
            $rate->setPrice($dataMethod->getCost());
        }
        $rate->setCost($dataMethod->getListCost());

        return $rate;
    }

    protected function _getErrorRate()
    {
        $error = Mage::getModel('shipping/rate_result_error');
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));

        $msg = $this->getConfigData('specificerrmsg');
        if ($customMsg = Mage::registry('mercadoenvios_msg')) {
            $msg = $msg . ' - ' . $customMsg;
        }
        $error->setErrorMessage($msg);

        return $error;
    }

    protected function _getEstimatedDate($dataTime)
    {
        $current = new Zend_Date();
        $current->setTime(0);
        $nextNotificationDate = $current->add($dataTime['shipping'], Zend_Date::HOUR);

        return Mage::helper('core')->formatDate($nextNotificationDate);
    }

    protected function _isAvailableRate($rateId)
    {
        if (empty($this->_available)) {
            $this->_available = explode(',', Mage::getStoreConfig('carriers/mercadoenvios/availablemethods'));
        }

        return in_array($rateId, $this->_available);
    }

    public function isActive()
    {
        if (!Mage::getStoreConfigFlag('payment/mercadopago_standard/active')) {
            return false;
        }
        if (!Mage::helper('mercadopago_mercadoenvios')->isCountryEnabled()) {
            return false;
        }

        return parent::isActive();
    }

    public function isTrackingAvailable()
    {
        return true;
    }

}
