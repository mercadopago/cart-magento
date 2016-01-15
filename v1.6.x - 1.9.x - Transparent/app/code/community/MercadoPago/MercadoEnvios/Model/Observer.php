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

    /**
     * @param $observer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function trackingPopup($observer)
    {
        $shippingInfoModel = Mage::getModel('shipping/info')->loadByHash(Mage::app()->getRequest()->getParam('hash'));

        if ($url = Mage::helper('mercadopago_mercadoenvios')->getTrackingUrlByShippingInfo($shippingInfoModel)) {
            Mage::app()->getRequest()->setDispatched(true);
            Mage::app()->getResponse()->setRedirect($url);
        }
    }

    public function createShipment($observer)
    {

        $merchant_order = $observer->getMerchantOrder();
        if (!count($merchant_order['shipments']) > 0) {
            return;
        }
        $data = $observer->getPayment();
        $order = Mage::getModel('sales/order')->loadByIncrementId($data["external_reference"]);

        //if order has shipments, status is updated. If it doesn't the shipment is created.
        if ($merchant_order['shipments'][0]['status'] == 'ready_to_ship') {
            if ($order->hasShipments()){
                $shipment = $order->getShipmentsCollection()->getFirstItem();
            } else {
                $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment();
                $shipment->register();
                $order->setIsInProcess(true);
            }
            $shipment->setShippingLabel($merchant_order['shipments'][0]['id']);

            $helper = Mage::helper('mercadopago_mercadoenvios');
            $shipmentInfo = $helper->getShipmentInfo($merchant_order['shipments'][0]['id']);
            Mage::helper('mercadopago')->log("Shipment Info", 'mercadopago-notification.log', $shipmentInfo);
            $serviceInfo = $helper->getServiceInfo($merchant_order['shipments'][0]['service_id'], $merchant_order['site_id']);
            Mage::helper('mercadopago')->log("Service Info by service id", 'mercadopago-notification.log', $serviceInfo);
            if ($shipmentInfo && isset($shipmentInfo->tracking_number)) {
                $tracking['number'] = str_replace('#{trackingNumber}', $shipmentInfo->tracking_number, $serviceInfo->tracking_url);
                $tracking['title'] = MercadoPago_MercadoEnvios_Model_Shipping_Carrier_MercadoEnvios::CODE;
                $track = Mage::getModel('sales/order_shipment_track')->addData($tracking);
                $shipment->addTrack($track);
                Mage::helper('mercadopago')->log("Track added", 'mercadopago-notification.log', $track);
            }

            Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($order)
                ->save();
        }
    }


}