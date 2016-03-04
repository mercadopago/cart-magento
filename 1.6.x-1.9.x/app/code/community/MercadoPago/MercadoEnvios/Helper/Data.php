<?php

class MercadoPago_MercadoEnvios_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    const XML_PATH_ATTRIBUTES_MAPPING = 'carriers/mercadoenvios/attributesmapping';
    const ME_LENGTH_UNIT = 'cm';
    const ME_WEIGHT_UNIT = 'gr';
    const ME_SHIPMENT_URL = 'https://api.mercadolibre.com/shipments/';
    const ME_SHIPMENT_LABEL_URL = 'https://api.mercadolibre.com/shipment_labels';
    const ME_SHIPMENT_TRACKING_URL = 'https://api.mercadolibre.com/sites/';

    protected $_mapping;
    protected $_products = [];

    public static $enabled_methods = ['mla', 'mlb', 'mlm'];

    /**
     * @param $quote Mage_Sales_Model_Quote
     */
    public function getDimensions($items)
    {
        $width = 0;
        $height = 0;
        $length = 0;
        $weight = 0;
        $helperItem = Mage::helper('mercadopago_mercadoenvios/itemData');
        foreach ($items as $item) {
            if (!$helperItem->itemHasChildren($item)) {
                $width += $this->_getShippingDimension($item, 'width');
                $height += $this->_getShippingDimension($item, 'height');
                $length += $this->_getShippingDimension($item, 'length');
                $weight += $this->_getShippingDimension($item, 'weight');
            }
        }
        $height = ceil($height);
        $width = ceil($width);
        $length = ceil($length);
        $weight = ceil($weight);

        if (!($height > 0 && $length > 0 && $width > 0 && $weight > 0)) {
            $this->log('Invalid dimensions in cart:', ['width'=>$width,'height'=>$height,'length'=>$length,'weight'=>$weight,]);
            Mage::throwException('Invalid dimensions cart');
        }

        return $height . 'x' . $width . 'x' . $length . ',' . $weight;

    }

    /**
     * @param $item Mage_Sales_Model_Quote_Item
     */
    protected function _getShippingDimension($item, $type)
    {
        $attributeMapped = $this->_getConfigAttributeMapped($type);
        if (!empty($attributeMapped)) {
            if (!isset($this->_products[$item->getProductId()])) {
                $this->_products[$item->getProductId()] = Mage::getModel('catalog/product')->load($item->getProductId());
            }
            $product = $this->_products[$item->getProductId()];
            $helperItem = Mage::helper('mercadopago_mercadoenvios/itemData');
            $result = $product->getData($attributeMapped);
            $result = $this->getAttributesMappingUnitConversion($type, $result);
            $qty = $helperItem->itemGetQty($item);
            $result = $result * $qty;
            if (empty($result)) {
                $this->log('Invalid dimension product: PRODUCT ', $item->getData());
                Mage::throwException('Invalid dimensions product');
            }

            return $result;
        }

        return 0;
    }

    protected function _getConfigAttributeMapped($type)
    {
        return (isset($this->getAttributeMapping()[$type]['code'])) ? $this->getAttributeMapping()[$type]['code'] : null;
    }

    public function getAttributeMapping()
    {
        if (empty($this->_mapping)) {
            $mapping = Mage::getStoreConfig(self::XML_PATH_ATTRIBUTES_MAPPING);
            $mapping = unserialize($mapping);
            $mappingResult = [];
            foreach ($mapping as $key => $map) {
                $mappingResult[$key] = ['code' => $map['attribute_code'], 'unit' => $map['unit']];
            }
            $this->_mapping = $mappingResult;
        }

        return $this->_mapping;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
            $quote = Mage::getModel('checkout/cart')->getQuote();
        }

        return $quote;
    }

    public function isMercadoEnviosMethod($method)
    {
        $shippingMethod = substr($method, 0, strpos($method, '_'));

        return ($shippingMethod == MercadoPago_MercadoEnvios_Model_Shipping_Carrier_MercadoEnvios::CODE);
    }

    /**
     * @param $attributeType string
     * @param $value         string
     *
     * @return string
     */
    public function getAttributesMappingUnitConversion($attributeType, $value)
    {
        $this->_getConfigAttributeMapped($attributeType);

        if ($attributeType == 'weight') {
            //check if needs conversion
            if ($this->_mapping[$attributeType]['unit'] != self::ME_WEIGHT_UNIT) {
                $unit = new Zend_Measure_Weight((float)$value);
                $unit->convertTo(Zend_Measure_Weight::GRAM);

                return $unit->getValue();
            }

        } elseif ($this->_mapping[$attributeType]['unit'] != self::ME_LENGTH_UNIT) {
            $unit = new Zend_Measure_Length((float)$value);
            $unit->convertTo(Zend_Measure_Length::CENTIMETER);

            return $unit->getValue();
        }

        return $value;
    }

    public function getFreeMethod($request)
    {
        $freeMethod = Mage::getStoreConfig('carriers/mercadoenvios/free_method');
        if (!empty($freeMethod)) {
            if (!Mage::getStoreConfigFlag('carriers/mercadoenvios/free_shipping_enable')) {
                return $freeMethod;
            } else {
                if (Mage::getStoreConfig('carriers/mercadoenvios/free_shipping_subtotal') <= $request->getPackageValue()) {
                    return $freeMethod;
                }
            }
        }

        return null;
    }

    public function isCountryEnabled()
    {
        return (in_array(Mage::getStoreConfig('payment/mercadopago/country'), self::$enabled_methods));
    }

    public function getTrackingUrlByShippingInfo($_shippingInfo)
    {
        $tracking = Mage::getModel('sales/order_shipment_track');
        $tracking = $tracking->getCollection()
            ->addFieldToFilter(
                ['entity_id', 'parent_id', 'order_id'],
                [
                    ['eq' => $_shippingInfo->getTrackId()],
                    ['eq' => $_shippingInfo->getShipId()],
                    ['eq' => $_shippingInfo->getOrderId()],
                ]
            )
            ->setPageSize(1)
            ->setCurPage(1)
            ->load();

        foreach ($_shippingInfo->getTrackingInfo() as $track) {
            $lastTrack = array_pop($track);
            if (isset($lastTrack['title']) && $lastTrack['title'] == MercadoPago_MercadoEnvios_Model_Observer::CODE) {
                $item = array_pop($tracking->getItems());
                if ($item->getId()) {
                    return $item->getDescription();
                }
            }
        }

        return '';
    }

    public function getTrackingPrintUrl($shipmentId)
    {
        if ($shipmentId) {
            if ($shipment = Mage::getModel('sales/order_shipment')->load($shipmentId)) {
                if ($shipment->getShippingLabel()) {
                    $params = [
                        'shipment_ids'  => $shipment->getShippingLabel(),
                        'response_type' => 'zpl2',
                        'access_token'  => Mage::helper('mercadopago')->getAccessToken()
                    ];

                    return self::ME_SHIPMENT_LABEL_URL . '?' . http_build_query($params);
                }
            }
        }

        return '';
    }

    public function getShipmentInfo($shipmentId)
    {
        $client = new Varien_Http_Client(self::ME_SHIPMENT_URL . $shipmentId);
        $client->setMethod(Varien_Http_Client::GET);
        $client->setParameterGet('access_token', Mage::helper('mercadopago')->getAccessToken());

        try {
            $response = $client->request();
        } catch (Exception $e) {
            $this->log($e);
            throw new Exception($e);
        }

        return json_decode($response->getBody());
    }

    public function getServiceInfo($serviceId, $country)
    {
        $client = new Varien_Http_Client(self::ME_SHIPMENT_TRACKING_URL . $country . '/shipping_services');
        $client->setMethod(Varien_Http_Client::GET);
        try {
            $response = $client->request();
        } catch (Exception $e) {
            $this->log($e);
            throw new Exception($e);
        }

        $response = json_decode($response->getBody());
        foreach ($response as $result) {
            if ($result->id == $serviceId) {
                return $result;
            }
        }

        return '';
    }

    public function log($message, $array = null, $level = Zend_Log::ERR, $file = "mercadoenvios.log")
    {
        $actionLog = Mage::getStoreConfig('carriers/mercadoenvios/log');
        if ($actionLog) {
            if (!is_null($array)) {
                $message .= " - " . json_encode($array);
            }

            Mage::log($message, $level, $file, $actionLog);
        }
    }

}