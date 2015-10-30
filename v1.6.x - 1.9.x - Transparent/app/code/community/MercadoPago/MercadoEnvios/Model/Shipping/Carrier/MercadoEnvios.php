<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Usa
 * @copyright   Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * UPS shipping implementation
 *
 * @category    Mage
 * @package     Mage_Usa
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MercadoPago_MercadoEnvios_Model_Shipping_Carrier_MercadoEnvios
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = 'mercadoenvios';
    protected $_methods;


    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Result|bool|null
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
            $allowedMethods[$method['shipping_method_id']] = $method['name'];
        }

        return $allowedMethods;
    }

    protected function getDataAllowedMethods()
    {
        if (empty($this->_methods)) {
            $quote = $this->_getQuote();

            $shippingAddress = $quote->getShippingAddress();
            if (empty($shippingAddress)) {
                return null;
            }
            $postcode = $shippingAddress->getPostcode();

            $client_id = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
            $client_secret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
            $mp = Mage::helper('mercadopago')->getApiInstance($client_id, $client_secret);

            $params = array(
                "dimensions" => "30x30x30,500",
                "zip_code"   => $postcode,
//            "free_method" => "73328" // optional
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


    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
            $quote = Mage::getModel('checkout/cart')->getQuote();
        }

        return $quote;
    }

}
