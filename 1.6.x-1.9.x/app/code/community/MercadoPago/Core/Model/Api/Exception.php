<?php

/**
 * Exception which thrown by MercadoPago API in case of processable error codes
 */
class MercadoPago_Core_Model_Api_Exception
    extends Mage_Core_Exception
{

    const GENERIC_USER_MESSAGE = "We could not process your payment in this moment. Please check the form data and retry later";

    protected $messagesMap;

    /**
     * Get error message which can be displayed to website user
     *
     * @return string
     */
    public function getUserMessage($error=null)
    {
        if (!empty($error)) {
            if (Mage::getStoreConfigFlag('payment/mercadopago/debug_mode')) {
                return $error['description'];
            } else {
                $code = $error['code'];
                if (isset($this->messagesMap[$code])) {
                    return Mage::helper('mercadopago')->__($this->messagesMap[$code]);
                }
            }
        }

        return Mage::helper('mercadopago')->__(self::GENERIC_USER_MESSAGE);
    }
}
