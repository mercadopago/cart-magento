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
            $args = func_get_args();
            $qtyArgs = count($args);
            if ($qtyArgs>2){
                $message = Mage::helper('mercadopago')->__($this->messagesMap[$key],$args[1],$args[2]);
            } elseif ($qtyArgs>1) {
                $message = Mage::helper('mercadopago')->__($this->messagesMap[$key],$args[1]);
            } else {
                $message = Mage::helper('mercadopago')->__($this->messagesMap[$key]);
            }
            return $message;
        }

        return '';
    }
}