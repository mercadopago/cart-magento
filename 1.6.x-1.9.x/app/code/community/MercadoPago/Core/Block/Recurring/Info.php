<?php

class MercadoPago_Core_Block_Recurring_Info
    extends Mage_Payment_Block_Info_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mercadopago/standard/info.phtml');
    }


    public function getOrder()
    {
        return $this->getInfo();
    }

    public function getInfoPayment()
    {
        $order_id = $this->getInfo()->getOrder()->getIncrementId();
        $info_payments = Mage::getModel('mercadopago/core')->getInfoPaymentByOrder($order_id);

        return $info_payments;
    }
}
