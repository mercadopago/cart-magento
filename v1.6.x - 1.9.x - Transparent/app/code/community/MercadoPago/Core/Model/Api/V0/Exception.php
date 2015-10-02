<?php

/**
 * Exception which thrown by MercadoPago API in case of processable error codes
 */
class MercadoPago_Core_Model_Api_V0_Exception extends MercadoPago_Core_Model_Api_Exception
{
    protected $messagesMap =
        array(
            106 => 'Cannot operate between users from different countries.',
            109 => 'Payment method does not process installments.',
            129 => 'Cannot pay this amount with this paymentMethod.',
            150 => 'You user cannot do payments currently',
            151 => 'You user cannot do payments currently with this payment method.',
            204 => 'Unavailable paymentmethod currently.',
            801 => 'Already posted the same request in the last minute.',
            'campaign_code_doesnt_match' => "Doesn't find a campaign with the given code."
        );

}