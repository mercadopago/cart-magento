<?php

class MercadoPago_MercadoEnvios_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    protected $_mapping;
    /**
     * @param $quote Mage_Sales_Model_Quote
     */
    public function getDimensions($items)
    {
        $width = 0;
        $height = 0;
        $length = 0;
        $weight = 0;
        foreach ($items as $item) {
            $width += $this->_getShippingDimension($item, 'width');
            $height += $this->_getShippingDimension($item, 'height');
            $length += $this->_getShippingDimension($item, 'length');
            $weight += $this->_getShippingDimension($item, 'weight');
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
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $result = $product->getData($attributeMapped);
            $result = $result * $item->getQty();

            return $result;
        }
        return 0;
    }

    protected function _getConfigAttributeMapped($type)
    {
        return (isset($this->getAttributeMapping()[$type]))?$this->getAttributeMapping()[$type]:null;
    }

    public function getAttributeMapping() {
        if (empty($this->_mapping)) {
            $mapping = Mage::getStoreConfig('carriers/mercadoenvios/attributesmapping');
            $mapping = unserialize($mapping);
            $mappingResult = [];
            foreach ($mapping as $map) {
                $mappingResult[$map['OcaCode']] = $map['MagentoCode'];
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
        $shippingMethod = substr($method,0,strpos($method,'_'));
        return ($shippingMethod == MercadoPago_MercadoEnvios_Model_Shipping_Carrier_MercadoEnvios::CODE);
    }
}