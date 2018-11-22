<?php

class MercadoPago_MercadoEnvios_Model_Observer
{

    protected $_useMercadoEnvios;
    const CODE = 'MercadoEnvios';

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
            $helper = Mage::helper('mercadopago_mercadoenvios');
            $shipment = Mage::registry('current_shipment');
            $shippingCode = Mage::getModel('sales/order')->load($shipment->getOrderId())->getShippingMethod();
            if (!$helper->isMercadoEnviosMethod($shippingCode)) {
                return;
            }
            $shipmentId = $shipment->getId();
            $block->addButton('print_shipment_label', array(
                'label'   => 'Print shipping label',
                'onclick' => 'window.open(\' ' . $helper->getTrackingPrintUrl($shipmentId) . '\')',
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
            $controller = $observer->getControllerAction();
            $controller->getResponse()->setRedirect($url);
            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
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
            if ($order->hasShipments()) {
                $shipment = Mage::getModel('sales/order_shipment')->load($order->getId(), 'order_id');
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
                $tracking['number'] = $shipmentInfo->tracking_number;
                $tracking['description'] = str_replace('#{trackingNumber}', $shipmentInfo->tracking_number, $serviceInfo->tracking_url);
                $tracking['title'] = self::CODE;

                $existingTracking = Mage::getModel('sales/order_shipment_track')->load($shipment->getOrderId(), 'order_id');
                if ($existingTracking->getId()) {
                    $track = $shipment->getTrackById($existingTracking->getId());
                    $track->setNumber($tracking['number'])
                        ->setDescription($tracking['description'])
                        ->setTitle($tracking['title'])
                        ->save();
                } else {
                    $track = Mage::getModel('sales/order_shipment_track')->addData($tracking);
                    $shipment->addTrack($track);
                }

                Mage::helper('mercadopago')->log("Track added", 'mercadopago-notification.log', $track);
            }

            Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($order)
                ->save();
        }
    }

    public function setShippingParams($observer)
    {
        $order = $observer->getOrder();
        $method = $order->getShippingMethod();
        $shippingCost = $order->getBaseShippingAmount();
        $paramsME = array();
        if (Mage::helper('mercadopago_mercadoenvios')->isMercadoEnviosMethod($method)) {
            $shippingAddress = $order->getShippingAddress();
            $zipCode = $shippingAddress->getPostcode();
            $defaultShippingId = substr($method, strpos($method, '_') + 1);
            $helperMe = Mage::helper('mercadopago_mercadoenvios');
            $paramsME = array(
                'mode'                    => 'me2',
                'zip_code'                => $zipCode,
                'default_shipping_method' => intval($defaultShippingId),
                'dimensions'              => $helperMe->getDimensions($helperMe->getAllItems($order->getAllItems()))
            );
            if ($shippingCost == 0) {
                $paramsME['free_methods'] = array(array('id' => intval($defaultShippingId)));
            }
        }

        $observer->getParams()->setValues($paramsME);
        Mage::helper('mercadopago_mercadoenvios')->log('REQUEST SHIPMENT ME: ', $paramsME, Zend_Log::INFO);

        return $observer;
    }

    public function setOrderShipmentData($observer)
    {
        $observerData = $observer->getData();

        $orderId = $observerData['orderId'];
        $shipmentData = $observerData['shipmentData'];
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $method = $order->getShippingMethod();
        if (Mage::helper('mercadopago_mercadoenvios')->isMercadoEnviosMethod($method)) {
            $methodId = $shipmentData['shipping_option']['shipping_method_id'];
            $name = $shipmentData['shipping_option']['name'];
            $order->setShippingMethod('mercadoenvios_' . $methodId);

            $estimatedDate = Mage::helper('core')->formatDate($shipmentData['shipping_option']['estimated_delivery']['date']);
            $estimatedDate = Mage::helper('mercadopago')->__('(estimated date %s)', $estimatedDate);
            $shippingDescription = 'MercadoEnvíos - ' . $name . ' ' . $estimatedDate;
            $order->setShippingDescription($shippingDescription);
            try {
                $order->save();
                Mage::helper('mercadopago_mercadoenvios')->log('Order ' . $order->getIncrementId() . ' shipping data setted ',$shipmentData, Zend_Log::INFO);
            } catch (Exception $e) {
                Mage::helper('mercadopago')->log("error when update shipment data: " . $e, 'mercadopago.log');
                Mage::helper('mercadopago_mercadoenvios')->log($e);
            }
        }
    }

    public function validateShippingMethod()
    {
        $selectedMethods = Mage::getStoreConfig('carriers/mercadoenvios/availablemethods');
        $validate = true;
        if (Mage::getStoreConfig('carriers/mercadoenvios/active')){
            if (empty($selectedMethods)) {
                $validate = false;
            } else {
                $methods = Mage::getModel('mercadopago_mercadoenvios/adminhtml_source_shipping_method')->getAvailableCodes();
                $currentMethods = explode(',', Mage::getStoreConfig('carriers/mercadoenvios/availablemethods', '0'));
                foreach ($currentMethods as $currentMethod) {
                    if (!in_array($currentMethod,$methods)) {
                        $validate = false;
                    }
                }
            }
        }

        if (!$validate){
            Mage::getConfig()->saveConfig('carriers/mercadoenvios/active', '0');
            Mage::throwException(Mage::helper('mercadopago_mercadoenvios')->__('MercadoEnvíos - Please enable a shipping method at least'));
        }
    }

}
