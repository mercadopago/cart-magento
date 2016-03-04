<?php

class MercadoPago_Core_Model_Discount_Coupon_Invoice
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    protected $_code = 'discount_coupon';

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $amount = $order->getDiscountCouponAmount();
        $baseAmount = $order->getBaseDiscountCouponAmount();
        if ($amount) {
            $invoice->setDiscountCouponAmount($amount);
            $invoice->setDiscountCouponAmount($baseAmount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseAmount);
        }

        return $this;
    }

}