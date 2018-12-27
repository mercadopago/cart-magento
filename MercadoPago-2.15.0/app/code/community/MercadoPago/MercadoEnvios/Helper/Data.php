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
    protected $_products = array();

    public static $enabled_methods = array('mla', 'mlb', 'mlm');


    /**
     * @param $quote Mage_Sales_Model_Quote
     */
    public function getDimensions($items)
    {
        $width = 0;
        $height = 0;
        $length = 0;
        $weight = 0;
        $bulk = 0;
        $helperItem = Mage::helper('mercadopago_mercadoenvios/itemData');
        $helperCarrier = Mage::helper('mercadopago_mercadoenvios/carrierData');
        foreach ($items as $item) {
            $tempWidth = $helperCarrier->_getShippingDimension($item, 'width');
            $tempHeight = $helperCarrier->_getShippingDimension($item, 'height');
            $tempLength = $helperCarrier->_getShippingDimension($item, 'length');
            $tempWeight = $helperCarrier->_getShippingDimension($item, 'weight');
            $qty = $helperItem->itemGetQty($item);
            $bulk += ($tempWidth * $tempHeight * $tempLength) * $qty;
            $width += $tempWidth * $qty;
            $height += $tempHeight * $qty;
            $length += $tempLength * $qty;
            $weight += $tempWeight * $qty;
        }
        $height = ceil($height);
        $width = ceil($width);
        $length = ceil($length);
        $weight = ceil($weight);

        $helperCarrier->validateCartDimension($height, $width, $length, $weight);
        $bulk = ceil(pow($bulk, 1/3));

        return $bulk . 'x' . $bulk . 'x' . $bulk . ',' . $weight;

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
                array('entity_id', 'parent_id', 'order_id'),
                array(
                    array('eq' => $_shippingInfo->getTrackId()),
                    array('eq' => $_shippingInfo->getShipId()),
                    array('eq' => $_shippingInfo->getOrderId()),
                )
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
                    $params = array(
                        'shipment_ids'  => $shipment->getShippingLabel(),
                        'response_type' => Mage::getStoreConfig('carriers/mercadoenvios/shipping_label'),
                        'access_token'  => Mage::helper('mercadopago')->getAccessToken()
                    );

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

    /**
     * Return items for further shipment rate evaluation. We need to pass children of a bundle instead passing the
     * bundle itself, otherwise we may not get a rate at all (e.g. when total weight of a bundle exceeds max weight
     * despite each item by itself is not)
     *
     * @return array
     */
    public function getAllItems($allItems)
    {
        $items = array();
        foreach ($allItems as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                // Don't process children here - we will process (or already have processed) them below
                continue;
            }

            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                        $items[] = $child;
                    }
                }
            } else {
                // Ship together - count compound item as one solid
                $items[] = $item;
            }
        }

        return $items;
    }
}
