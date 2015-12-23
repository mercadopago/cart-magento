<?php

class MercadoPago_MercadoEnvios_Model_Observer
{

    protected $_useMercadoEnvios;

    public function filterActivePaymentMethods($observer)
    {
        if ($this->_useMercadoEnvios()) {
            $event = $observer->getEvent();
            $method = $event->getMethodInstance();
            $result = $event->getResult();
            if ($method->getCode() != 'mercadopago_standard') {
                $result->isAvailable = false;
            }
        }
    }

    protected function _useMercadoEnvios()
    {
        if (empty($this->_useMercadoEnvios)) {
            $quote = Mage::helper('mercadopago_mercadoenvios')->getQuote();
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            $this->_useMercadoEnvios = Mage::helper('mercadopago_mercadoenvios')->isMercadoEnviosMethod($shippingMethod);
        }

        return $this->_useMercadoEnvios;
    }

    public function checkAndValidateData()
    {
        $country = Mage::getStoreConfig('payment/mercadopago/country');
        $code = Mage::getModel('mercadopago/source_country')->getCodeByValue($country);
        Mage::getConfig()->saveConfig('carriers/mercadoenvios/specificcountry', $code);
    }

    public function addPrintButton($observer)
    {
        $block = $observer->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View) {
            $shipmentId = Mage::app()->getRequest()->getParam('shipment_id');
            $block->addButton('print_shipment_label', array(
                'label'   => 'Print shipping label',
                'onclick' => 'window.open(\' ' . Mage::helper('mercadopago_mercadoenvios')->getTrackingPrintUrl($shipmentId) . '\')',
                'class'   => 'go'
            ));
        }
    }

    public function trackingPopup($observer)
    {
        $shippingInfoModel = Mage::getModel('shipping/info')->loadByHash(Mage::app()->getRequest()->getParam('hash'));

        if ($url = Mage::helper('mercadopago_mercadoenvios')->getTrackingUrlByShippingInfo($shippingInfoModel)) {
            Mage::app()->getRequest()->setDispatched(true);
            Mage::app()->getResponse()->setRedirect($url);
        }
    }

}