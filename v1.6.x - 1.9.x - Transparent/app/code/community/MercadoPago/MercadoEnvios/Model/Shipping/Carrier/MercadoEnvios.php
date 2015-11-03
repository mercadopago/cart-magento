<?php

class MercadoPago_MercadoEnvios_Model_Shipping_Carrier_MercadoEnvios
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'mercadoenvios';


    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'standard' => 'Standard delivery',
            'express'  => 'Express delivery',
        );
    }

}
