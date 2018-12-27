<?php

class MercadoPago_Core_Model_Recurring_Payment
    extends Mage_Payment_Model_Method_Abstract
    implements Mage_Payment_Model_Recurring_Profile_MethodInterface
{
    protected $_formBlockType = 'mercadopago/recurring_form';
    protected $_infoBlockType = 'mercadopago/recurring_info';

    protected $_code = 'mercadopago_recurring';

    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;

    const LOG_FILE = 'mercadopago-recurring.log';

    public function isAvailable($quote = null)
    {
        $parent = parent::isAvailable($quote);
        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        $standard = (!empty($clientId) && !empty($clientSecret));

        if (!$parent || !$standard) {
            return false;
        }

        if ($quote != null) {
            $enabled = Mage::getStoreConfig('payment/mercadopago_recurring/active');
            if ($enabled) {
                $items = $quote->getAllItems();
                foreach ($items as $item) {
                    if (!$item->getIsNominal()) {
                        return false;
                    }
                }
            }
        }

        return Mage::helper('mercadopago')->isValidClientCredentials($clientId, $clientSecret);

    }

    public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
    {
        $core = Mage::getModel('mercadopago/core');
        $response = $core->getRecurringPayment($profile->getReferenceId());
        if ($response['status'] == 201 || $response['status'] == 200) {
            return true;
        }
        return false;
    }

    /**
     * Submit to the gateway
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Payment_Model_Info $paymentInfo
     */
    public function submitRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile, Mage_Payment_Model_Info $paymentInfo)
    {
        if ($profile == null || $paymentInfo == null) {
            return;
        }

        $date = new DateTime($profile->getStartDatetime());
        $date->modify('+3 minute');
        $startDate = $date->format("Y-m-d\TH:i:s.mO");

        $daysModifier = 1;
        $end = null;
        $periodUnit = null;
        $periodFrequency = null;
        switch ($profile->getPeriodUnit()) {
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_DAY:
                $periodUnit = 'days';
                $periodFrequency = $profile->getPeriodFrequency();
                break;
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_WEEK:
                $periodUnit = 'days';
                $periodFrequency = $profile->getPeriodFrequency() * 7;
                $daysModifier = 7;
                break;
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_SEMI_MONTH:
                $periodUnit = 'days';
                $periodFrequency = $profile->getPeriodFrequency() * 14;
                $daysModifier = 14;
                break;
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_MONTH:
                $periodUnit = 'months';
                $periodFrequency = $profile->getPeriodFrequency();
                $end = $date->modify('+' . $profile->getPeriodMaxCycles() . ' ' . $periodUnit);
                break;
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_YEAR:
                $periodUnit = 'days';
                $periodFrequency = $profile->getPeriodFrequency() * 365;
                $daysModifier = 365;
                break;
        }

        if (!isset($end)) {
            $end = $date->modify('+' . $profile->getPeriodMaxCycles() * $daysModifier . ' ' . $periodUnit);
        }
        $end->modify('-3 minute');

        $endDate = $end->format("Y-m-d\TH:i:s.mO");

        $backUrl = Mage::getStoreConfig('payment/mercadopago_recurring/back_url');

        $preapprovalData = array(
            "payer_email" => $profile->getOrderInfo()['customer_email'],
            "back_url" => $backUrl,
            "reason" => $profile->getScheduleDescription(),
            "external_reference" => $profile->getId(),
            "auto_recurring" => array(
                "frequency" => $periodFrequency,
                "frequency_type" => $periodUnit,
                "transaction_amount" => $profile->getBillingAmount() + $profile->getShippingAmount(),
                "currency_id" => $profile->getCurrencyCode(),
                "start_date" => $startDate,
                "end_date" => $endDate
            )
        );

        $this->_sendPreapprovalPaymentRequest($profile, $preapprovalData);
    }

    protected function _sendPreapprovalPaymentRequest($profile, $preapproval_data) {
        $clientId = Mage::getStoreConfig('payment/mercadopago_recurring/client_id');
        $clientSecret = Mage::getStoreConfig('payment/mercadopago_recurring/client_secret');

        $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
        $sandbox = Mage::getStoreConfig('payment/mercadopago_recurring/sandbox_mode');

        if ($sandbox) {
            $mp->sandbox_mode(true);
        }

        $response = $mp->create_preapproval_payment($preapproval_data);
        if ($response['status'] == 201 || $response['status'] == 200) {
            if ($sandbox) {
                $redirectUrl = $response['response']['sandbox_init_point'];
            } else {
                $redirectUrl = $response['response']['init_point'];
            }
            Mage::getSingleton('customer/session')->setInitPoint($redirectUrl);
            $profile->setData('reference_id', $response['response']['id']);
            $profile->setData('schedule_description', $redirectUrl);
            $profile->setData('external_reference', $response['response']['id']);
        } else {
            Mage::throwException(Mage::helper('mercadopago')->__('Mercado Pago - Recurring Profile not created')
                . "\n" . $response['response']['message']);
        }
    }

    /**
     * Fetch details
     *
     * @param string $referenceId
     * @param Varien_Object $result
     */
    public function getRecurringProfileDetails($referenceId, Varien_Object $result)
    {
        $core = Mage::getModel('mercadopago/core');
        $response = $core->getRecurringPayment($referenceId);
        $value = $response ['response']['status'];
        switch ($value) {
            case 'authorized':
                $result->setIsProfileActive(true);
                break;
            case 'pending':
                $result->setIsProfilePending(true);
                break;
            case 'cancelled':
                $result->setIsProfileCanceled(true);
                break;
            case 'paused':
                $result->setIsProfileSuspended(true);
                break;
            case 'expired':
                $result->setIsProfileExpired(true);
                break;
        }

        $profile = Mage::registry('current_recurring_profile');
        $productId = $profile->getOrderItemInfo()['product_id'];
        $product = Mage::getModel('catalog/product')->load($productId);

        $actualAmount = $profile->getBillingAmount() + $profile->getShippingAmount();
        $newAmount = $response ['response']['auto_recurring']['transaction_amount'];

        if ($actualAmount != $newAmount) {
            $billingAmount = $newAmount - $profile->getShippingAmount();
            $profile->setBillingAmount($billingAmount);
        }

        $isAdmin = Mage::app()->getStore()->isAdmin();
        if ($isAdmin) {
            $this->getUpdateFromAdmin($product, $profile, $newAmount, $referenceId);
        }
    }

    protected function getUpdateFromAdmin($product, $profile, $newAmount, $referenceId)
    {
        $localAmount = $product->getPrice() + $profile->getShippingAmount();
        if ($localAmount != $newAmount) {
            $clientId = Mage::getStoreConfig('payment/mercadopago_recurring/client_id');
            $clientSecret = Mage::getStoreConfig('payment/mercadopago_recurring/client_secret');
            $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
            $response = $mp->update_preapproval_payment($referenceId, array("auto_recurring" => array("transaction_amount" => $localAmount)));
            if ($response['status'] == 201 || $response['status'] == 200) {
                $profile->setBillingAmount($localAmount);
                $this->_getSession()->addSuccess(__('Recurring Profile updated by Mercado Pago'));
            } else {
                $this->_getSession()->addError(__('Failed to update the recurring profile by Mercado Pago'));
                $this->_getSession()->addError($response['response']['message']);
            }
        }
    }

    /**
     * Check whether can get recurring profile details
     *
     * @return bool
     */
    public function canGetRecurringProfileDetails()
    {
        return true;
    }

    /**
     * Update data
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
    {
        $clientId = Mage::getStoreConfig('payment/mercadopago_recurring/client_id');
        $clientSecret = Mage::getStoreConfig('payment/mercadopago_recurring/client_secret');
        $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
        $core = Mage::getModel('mercadopago/core');
        $response = $core->getRecurringPayment($profile->getReferenceId());
        $newAmount = $response ['response']['auto_recurring']['transaction_amount'];
        $id = $profile->getData('schedule_description');
        $response = $mp->update_preapproval_payment($id, array("auto_recurring" => array("transaction_amount" => $newAmount)));
        if ($response['status'] == 201 || $response['status'] == 200) {
            $this->_getSession()->addSuccess(__('Recurring Profile updated by Mercado Pago'));
        } else {
            Mage::throwException(Mage::helper('mercadopago')->__('Failed to update the recurring profile by Mercado Pago'));
        }
    }

    /**
     * Manage status
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile)
    {
        $clientId = Mage::getStoreConfig('payment/mercadopago_recurring/client_id');
        $clientSecret = Mage::getStoreConfig('payment/mercadopago_recurring/client_secret');
        $mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);
        $sandbox = Mage::getStoreConfig('payment/mercadopago_recurring/sandbox_mode');
        if ($sandbox) {
            $mp->sandbox_mode(true);
        }
        $action = null;
        switch ($profile->getNewState()) {
            case Mage_Sales_Model_Recurring_Profile::STATE_CANCELED: $action = 'cancelled'; break;
            case Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED: $action = 'paused'; break;
            case Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE: $action = 'authorized'; break;
        }

        $id = explode('=' , $profile->getData('schedule_description'))[1];
        $response = $mp->update_preapproval_payment($id, array("status" => $action));
        if ($response['status'] == 201 || $response['status'] == 200) {
            $this->_getSession()->addSuccess(__('Recurring Profile updated by Mercado Pago'));
        } else {
            $this->_getSession()->addError(__('Failed to update the recurring profile by Mercado Pago'));
            $this->_getSession()->addError($response['status'] . ' ' . $response['response']['message']);
        }
    }

    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }

    public function getRecurringPaymentData()
    {
        $initPoint = Mage::getSingleton('customer/session')->getInitPoint();
        $arrayAssign = array(
            "init_point"      => $initPoint,
            "type_checkout"   => $this->getConfigData('type_checkout'),
            "iframe_width"    => $this->getConfigData('iframe_width'),
            "iframe_height"   => $this->getConfigData('iframe_height'),
            "banner_checkout" => $this->getConfigData('banner_checkout'),
            "status"          => 201
        );

        return $arrayAssign;
    }
}
