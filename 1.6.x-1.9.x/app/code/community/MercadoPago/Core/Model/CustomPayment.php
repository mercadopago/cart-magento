<?php

/**
 * Created by PhpStorm.
 * User: dami
 * Date: 20/10/15
 * Time: 17:26
 */
abstract class MercadoPago_Core_Model_CustomPayment
    extends Mage_Payment_Model_Method_Abstract
{

    protected $_canSaveCc = false;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canVoid = true;
    protected $_canCancelInvoice = true;
    protected $_isInitializeNeeded = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canReviewPayment = true;

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $parent = parent::isAvailable($quote);
        $accessToken = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_ACCESS_TOKEN);
        $publicKey = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_PUBLIC_KEY);
        $custom = (!empty($publicKey) && !empty($accessToken));

        if (!$parent || !$custom) {
            return false;
        }

        $secure = Mage::app()->getFrontController()->getRequest()->isSecure();
        if ($this->_code == 'mercadopago_custom' && strpos($accessToken, 'TEST') === false && !$secure) {
          return false;
        }

        return Mage::helper('mercadopago')->isValidAccessToken($accessToken);
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get admin checkout session namespace
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getAdminCheckout()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Retrieves Quote
     *
     * @param integer $quoteId
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId = null)
    {
        if (!empty($quoteId)) {
            return Mage::getModel('sales/quote')->load($quoteId);
        } else {
            if (Mage::app()->getStore()->isAdmin()) {
                return $this->_getAdminCheckout()->getQuote();
            } else {
                return $this->_getCheckout()->getQuote();
            }
        }
    }

    /**
     * Retrieves Order
     *
     * @param integer $incrementId
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder($incrementId)
    {
        return Mage::getModel('sales/order')->loadByIncrementId($incrementId);
    }
}
