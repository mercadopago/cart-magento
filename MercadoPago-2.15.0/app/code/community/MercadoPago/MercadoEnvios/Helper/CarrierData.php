<?php

class MercadoPago_MercadoEnvios_Helper_CarrierData
    extends Mage_Core_Helper_Abstract
{

    const XML_PATH_ATTRIBUTES_MAPPING = 'carriers/mercadoenvios/attributesmapping';
    const ME_LENGTH_UNIT = 'cm';
    const ME_WEIGHT_UNIT = 'gr';

    protected $_products = array();
    protected $_mapping;

    protected $_maxWeight = array('mla' => '25000', 'mlb' => '30000', 'mlm' => '');

    protected $_individualDimensions = array(
      'height' => array(
        'mla' => array('min' => '0', 'max' => '70'),
        'mlb' => array('min' => '2', 'max' => '105'),
        'mlm' => array('min' => '0', 'max' => '80')
      ),

      'width'  => array(
        'mla' => array('min' => '0', 'max' => '70'),
        'mlb' => array('min' => '11', 'max' => '105'),
        'mlm' => array('min' => '0', 'max' => '80')
      ),

      'length' => array(
        'mla' => array('min' => '0', 'max' => '70'),
        'mlb' => array('min' => '16', 'max' => '105'),
        'mlm' => array('min' => '0', 'max' => '120')
      ),

      'weight' => array(
        'mla' => array('min' => '0', 'max' => '25000'),
        'mlb' => array('min' => '0', 'max' => '30000'),
        'mlm' => array('min' => '0', 'max' => '70000')
      ),
    );

    protected $_globalMaxDimensions = array(
      'mla' => '210',
      'mlb' => '200',
      'mlm' => '347',
    );


    /**
     * @param $item Mage_Sales_Model_Quote_Item
     */
    public function _getShippingDimension($item, $type)
    {
        $attributeMapped = $this->_getConfigAttributeMapped($type);
        if (empty($attributeMapped)) {
            $helper = Mage::helper('mercadopago_mercadoenvios');
            $helper->log('Invalid attributes mapping: PRODUCT ', $item->getData());
            Mage::throwException('Invalid attributes mapping');
        }
        if (!isset($this->_products[$item->getProductId()])) {
            $this->_products[$item->getProductId()] = Mage::getModel('catalog/product')->load($item->getProductId());
        }
        $product = $this->_products[$item->getProductId()];
        $result = $product->getData($attributeMapped);
        $result = $this->getAttributesMappingUnitConversion($type, $result);
        $this->validateProductDimension($result, $type, $item);

        return $result;
    }

    protected function validateProductDimension($dimension, $type, $item)
    {
        $helper = Mage::helper('mercadopago_mercadoenvios');
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        if ((!ceil($dimension) > 0) or ($dimension > $this->_individualDimensions[$type][$country]['max']) or ($dimension < $this->_individualDimensions[$type][$country]['min'])) {
            $helper->log('Invalid dimension product: PRODUCT ', $item->getData());
            Mage::throwException('Invalid dimensions product');
        }
    }

    public function validateCartDimension($height, $width, $length, $weight)
    {
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        if (!isset($this->_globalMaxDimensions[$country])) {
            return;
        }
        $helper = Mage::helper('mercadopago_mercadoenvios');
        if (($height + $width + $length) > $this->_globalMaxDimensions[$country]) {
            $helper->log('Invalid dimensions in cart:', array('width' => $width, 'height' => $height, 'length' => $length, 'weight' => $weight));
            Mage::register('mercadoenvios_msg', $this->__('Package exceed maximum dimensions'));
            Mage::throwException('Invalid dimensions cart');
        }
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
            $mappingResult = array();
            foreach ($mapping as $key => $map) {
                $mappingResult[$key] = array('code' => $map['attribute_code'], 'unit' => $map['unit']);
            }
            $this->_mapping = $mappingResult;
        }

        return $this->_mapping;
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
}
