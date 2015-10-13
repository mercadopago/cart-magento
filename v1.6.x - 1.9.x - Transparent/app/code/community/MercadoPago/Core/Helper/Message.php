<?php

class MercadoPago_Core_Helper_Message
    extends Mage_Core_Helper_Abstract
{

    protected $messageMap;

    /**
     * @param      $key
     * @param null $args array()
     *
     * @return string
     */
    public function getMessage($key)
    {
        if (isset($this->messagesMap[$key])) {
            return $this->messagesMap[$key];
        }

        return '';
    }
}