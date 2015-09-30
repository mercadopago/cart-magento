<?php

/**
 * Exception which thrown by MercadoPago API in case of processable error codes
 */
class MercadoPago_Core_Model_MpV0Exception extends MercadoPago_Core_Model_MpException
{
    protected $messagesMap =
        array(
            205 => 'Enter the card number',
            208 => 'Enter the card expiration month',
            209 => 'Enter the card expiration year',
            212 => 'Enter the document type',
            213 => 'Enter the document subtype',
            214 => 'Enter the document number',
            220 => 'Enter the bank',
            221 => 'Enter the card holder name',
            224 => 'Enter the security code',
            'E301'=>'Card number is invalid.',
            'E302'=>'Security code is invalid.',
            316=>'Card Holder Name is invalid.',
            322=>'Document Type is invalid.',
            323=>'Document Sub Type is invalid.',
            324=>'Document Number is invalid.',
            325=>'Month is invalid.',
            326=>'Year is invalid.',
            'campaign_code_doesnt_match' => "Doesn't find a campaign with the given code."
        );

}
