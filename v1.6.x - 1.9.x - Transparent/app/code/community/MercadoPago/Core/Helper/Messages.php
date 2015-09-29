<?php

/**
 * Created by PhpStorm.
 * User: dami
 * Date: 29/09/15
 * Time: 17:28
 */
class MercadoPago_Core_Helper_Messages
    extends Mage_Core_Helper_Data
{
    const STATUS_BAD_REQUEST = 400;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const GENERIC_USER_MESSAGE = "We could not process your payment.";

    protected static $messagesMap = array(
        self::STATUS_BAD_REQUEST => array(
            1 => array('message'=> 'Params Error'),
            3 => array('message'=> 'Token must be for test'),
            5 => array('message'=> 'Must provide your access_token to proceed'),
            3000 => array(
                'message'=> 'You must provide your cardholder_name with your card data',
                'user_message' => 'Please, provide your cardholder name')
        ),
        self::STATUS_FORBIDDEN   => array(
            4    => array('message'=> 'The caller is not authorized to access this resource'),
            2041 => array('message'=> 'Only admin users can perform the requested action'),
            3002 => array('message'=> 'The caller is not authorized to perform this action'),
        ),
        self::STATUS_NOT_FOUND   => array(
            2000 => array('message'=>'Payment not found')
        )
    );

    public function getMessage($status, $code)
    {
        if (Mage::getStoreConfigFlag('payment/mercadopago/debug_mode')){
            if (isset($messagesMap[$status][$code])) {
                return self::$messagesMap[$status][$code]['message'];
            }
        } else {
            if (isset( self::$messagesMap[$status][$code]['user_message'])){
                return $this->__(self::$messagesMap[$status][$code]['user_message']);
            }
        }
        return $this->__(self::GENERIC_USER_MESSAGE);
    }
}