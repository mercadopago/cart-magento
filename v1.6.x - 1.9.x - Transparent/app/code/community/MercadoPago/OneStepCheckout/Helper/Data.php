<?php

class MercadoPago_OneStepCheckout_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ONS_ACTIVE = 'payment/mercadopago/osc_active';

    public function isOneStepCheckoutActive() {
        return Mage::getStoreConfigFlag(MercadoPago_OneStepCheckout_Helper_Data::XML_PATH_ONS_ACTIVE);
    }

}