<?php

abstract class MercadoPago_Core_Helper_Message_Abstract
    extends Mage_Core_Helper_Abstract
{

    public abstract function getMessageMap();


    /**
     * @param      $key
     * @param null $args array()
     *
     * @return string
     */
    public function getMessage($key)
    {
        $messageMap = $this->getMessageMap();
        if (isset($messageMap[$key])) {
            return $messageMap[$key];
        }

        return '';
    }

}