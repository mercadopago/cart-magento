<?php

/**
 * Exception which thrown by MercadoPago API in case of processable error codes from api v1
 */
class MercadoPago_Core_Model_Api_V1_Exception
    extends MercadoPago_Core_Model_Api_Exception
{
    protected $messagesMap = array(
            1000 => 'The items Quantity has exceeded the limits. We could not process your payment.',
            2001 => 'Already posted the same request in the last minute.',
            2017 => 'The transaction amount is invalid. We could not process your payment.',
            2020 => 'You user cannot do payments currently',
            2022 => 'You have exceeded the max number of refunds for this payment.',
            2024 => 'Payment too old to be refunded',
            2034 => 'You user cannot do payments currently',
            2039 => 'Coupon code is invalid',
            2040 => 'Your user e-mail does not exist. Please check data and retry later',
            2043 => 'Coupon code is invalid',
            3000 => 'You must provide your cardholder name with your card data',
            3011 => 'Not found payment method or credit card. Please check the form data and retry.',
            3012 => 'Security code is invalid. Please check the form data and retry.',
            3013 => 'Security code is a required field. Please check the form data and retry.',
            3014 => 'Payment method or credit card invalid. Please check the form data and retry.',
            3015 => 'Credit card number is invalid. Please check the form data and retry.',
            3016 => 'Credit card number is invalid. Please check the form data and retry.',
            3018 => 'Expiration month can not be empty. Please check the form data and retry.',
            3019 => 'Expiration year can not be empty. Please check the form data and retry.',
            3020 => 'Cardholder name can not empty. Please check the form data and retry.',
            3021 => 'Cardholder document number can not be empty. Please check the form data and retry.',
            3022 => 'Cardholder document type can not be empty. Please check the form data and retry.',
            3023 => 'Cardholder document subtype can not be empty. Please check the form data and retry.',
            3029 => 'Expiration month is invalid. Please check the form data and retry.',
            3030 => 'Expiration year is invalid. Please check the form data and retry.',
            4004 => 'Installments attribute can not be empty. Please check the form data and retry.',
            4026 => 'Coupon amount is invalid. Please check the form data and retry.',
            'campaign_code_doesnt_match' => "Doesn't find a campaign with the given code.",
            'amount_doesnt_match' => "Doesn't find a campaign to amount given",
            'transaction_amount_invalid' => "Amount discount is invalid"
        );

}
