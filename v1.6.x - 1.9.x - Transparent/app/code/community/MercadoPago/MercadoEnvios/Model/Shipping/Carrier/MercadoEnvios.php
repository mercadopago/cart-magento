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
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');
        foreach ($this->getAllowedMethods() as $methodId => $methodName) {
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
        $allowedMethods = [];
        foreach ($methods as $method) {
//            if ($this->_isAvailableRate($method['shipping_method_id'])) {
                $allowedMethods[$method['shipping_method_id']] = $method['name'];
//            }
        }

        return $allowedMethods;
    }

    protected function getDataAllowedMethods()
    {
        if (empty($this->_methods)) {
            $quote = Mage::helper('mercadopago_mercadoenvios')->getQuote();

            $shippingAddress = $quote->getShippingAddress();
            if (empty($shippingAddress)) {
                return null;
            }
            $postcode = $shippingAddress->getPostcode();

            $client_id = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
            $client_secret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
            $mp = Mage::helper('mercadopago')->getApiInstance($client_id, $client_secret);

            $params = array(
                "dimensions" => Mage::helper('mercadopago_mercadoenvios')->getDimensions($quote),
                "zip_code"   => $postcode,
            );
            $response = $mp->get("/shipping_options", $params);
            if ($response['status'] == 200) {
                $this->_methods = $response['response']['options'];
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
        new Varien_Object();
    }

    protected function _getRate($methodId)
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $dataMethod = $this->getDataMethod($methodId);
        $rate->setCarrier($this->_code);

        $estimatedDate = $this->_getEstimatedDate($dataMethod->getEstimatedDeliveryTime());
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod($methodId);
        $rate->setMethodTitle($dataMethod->getName() . ' ' . Mage::helper('mercadopago')->__('(estimated date %s)', $estimatedDate));
        $rate->setPrice($dataMethod->getCost());
        $rate->setCost($dataMethod->getListCost());

        return $rate;
    }

    protected function _getEstimatedDate($dataTime)
    {
        $current = new Zend_Date();
        $current->setTime(0);
        $nextNotificationDate = $current->add($dataTime['shipping'], Zend_Date::HOUR);

        return Mage::helper('core')->formatDate($nextNotificationDate);
    }
   
    protected function _isAvailableRate($rateId) {
        $available = $this->getConfigData('availablemethods');
        return in_array($rateId,$available);
    }

}
