<?php

class MercadoPago_Core_Helper_StatusMessage
    extends MercadoPago_Core_Helper_Message_Abstract
{
    protected $messagesMap = [
            "approved"   => [
                'title'   => 'Done, your payment was accredited!',
                'message' => ''
            ],

            "in_process" => [
                'title'   => 'We are processing the payment.',
                'message' => 'In less than 2 business days we will tell you by e-mail if it is accredited or if we need more information.'
            ],

            "authorized" => [
                'title'   => 'We are processing the payment.',
                'message' => 'In less than an hour we will send you by e-mail the result.'
            ],

            "pending"    => [
                'title'   => 'We are processing the payment.',
                'message' => 'In less than an hour we will send you by e-mail the result.'
            ],

            "rejected"   => [
                'title'   => 'We could not process your payment.',
                'message' => ''
            ],

            "cancelled"  => [
                'title'   => 'Payments were canceled.',
                'message' => 'Contact for more information.'
            ],

            "other"      => [
                'title'   => 'Thank you for your purchase!',
                'message' => ''
            ]
    ];

    public function getMessageMap()
    {
        return $this->messagesMap;
    }
}