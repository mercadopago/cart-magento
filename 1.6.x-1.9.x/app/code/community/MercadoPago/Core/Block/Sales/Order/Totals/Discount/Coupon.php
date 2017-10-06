<?php

class MercadoPago_Core_Block_Sales_Order_Totals_Discount_Coupon
    extends Mage_Core_Block_Abstract
{
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Add this total to parent
     */
    public function initTotals()
    {

        if ((float)$this->getSource()->getDiscountCouponAmount() == 0 || !Mage::getStoreConfigFlag('payment/mercadopago/consider_discount')) {
            return $this;
        }

        $total = new Varien_Object(array(
            'code'  => 'discount_coupon',
            'field' => 'discount_coupon_amount',
            'value' => $this->getSource()->getDiscountCouponAmount(),
            'label' => $this->__('Discount Mercado Pago'),
        ));
        $this->getParentBlock()->addTotalBefore($total, 'shipping');

        return $this;
    }
}
