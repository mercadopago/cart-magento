<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category       Payment Gateway
 * @package        MercadoPago
 * @author         Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 * @copyright      Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class MercadoPago_Core_Block_Custom_Info
    extends Mage_Payment_Block_Info_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mercadopago/custom/info.phtml');
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
