<?php

class MercadoPago_Core_Model_Discount_Coupon_Creditmemo
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    protected $_code = 'financing_cost';

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $amount = $order->getDiscountCouponAmount();
        $baseAmount = $order->getBaseDiscountCouponAmount();
        if ($amount) {
            $creditmemo->setDiscountCouponAmount($amount);
            $creditmemo->setBaseDiscountCouponAmount($baseAmount);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount);
        }

        return $this;
    }

}