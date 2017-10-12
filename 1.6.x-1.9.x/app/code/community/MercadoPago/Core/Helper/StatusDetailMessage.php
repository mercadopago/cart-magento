<?php

class MercadoPago_Core_Helper_StatusDetailMessage
    extends MercadoPago_Core_Helper_Message_Abstract
{
    protected $messagesMap = array(
            "cc_rejected_bad_filled_card_number"   => 'Check the card number.',
            "cc_rejected_bad_filled_date"          => 'Check the expiration date.',
            "cc_rejected_bad_filled_other"         => 'Check the data.',
            "cc_rejected_bad_filled_security_code" => 'Check the security code.',
            "cc_rejected_blacklist"                => 'We could not process your payment.',
            "cc_rejected_call_for_authorize"       => 'You must authorize to %s the payment of $ %s to Mercado Pago.',
            "cc_rejected_card_disabled"            => 'Call %s to activate your card.<br/>The phone is on the back of your card.',
            "cc_rejected_card_error"               => 'We could not process your payment.',
            "cc_rejected_duplicated_payment"       => 'You already made a payment by that value.<br/>If you need to repay, use another card or other payment method.',
            "cc_rejected_high_risk"                => 'Your payment was rejected.<br/>Choose another payment method, we recommend cash methods.',
            "cc_rejected_insufficient_amount"      => 'Your %s do not have sufficient funds.',
            "cc_rejected_invalid_installments"     => '%s does not process payments in %s installments.',
            "cc_rejected_max_attempts"             => 'You have got to the limit of allowed attempts.<br/>Choose another card or another payment method.',
            "cc_rejected_other_reason"             => '%s did not process the payment.',
    );

    public function getMessageMap()
    {
        return $this->messagesMap;
    }

}
