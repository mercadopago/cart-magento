<?php

class MercadoPago_MercadoEnvios_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    const XML_PATH_ATTRIBUTES_MAPPING = 'carriers/mercadoenvios/attributesmapping';
    const ME_LENGTH_UNIT = 'cm';
    const ME_WEIGHT_UNIT = 'gr';

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
        return (isset($this->getAttributeMapping()[$type])) ? $this->getAttributeMapping()[$type] : null;
    }

    public function getAttributeMapping()
    {
        if (empty($this->_mapping)) {
            $mapping = Mage::getStoreConfig(self::XML_PATH_ATTRIBUTES_MAPPING);
            $mapping = unserialize($mapping);
            $mappingResult = [];
            foreach ($mapping as $map) {
                $mappingResult[$map['me_code']] = ['code' => $map['attribute_code'], 'unit' => $map['unit']];
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
     * @param $value string
     * @return string
     */
    public function getAttributesMappingUnitConversion($attributeType, $value)
    {
        $this->_getConfigAttributeMapped($attributeType);

        if ($attributeType == 'weight') {
            //check if needs conversion
            if ($this->_mapping[$attributeType]['unit'] != self::ME_WEIGHT_UNIT) {
                $unit = new Zend_Measure_Weight($value);
                $unit->convertTo(Zend_Measure_Weight::GRAM);

                return $unit->getValue();
            }

        } elseif ($this->_mapping[$attributeType]['unit'] != self::ME_LENGTH_UNIT) {
            $unit = new Zend_Measure_Length($value);
            $unit->convertTo(Zend_Measure_Length::CENTIMETER);

            return $unit->getValue();
        }

        return $value;
    }

}