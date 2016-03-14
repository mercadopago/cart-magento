<?php

class MercadoPago_Core_Model_Discount_Coupon
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_code = 'discount_coupon';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->_getDiscountCondition($address)) {

            $postData = Mage::app()->getRequest()->getPost();
            parent::collect($address);

            $balance = $postData['mercadopago-discount-amount'] * -1;

            $address->setDiscountCouponAmount($balance);
            $address->setBaseDiscountCouponAmount($balance);

            $this->_setAmount($balance);
            $this->_setBaseAmount($balance);

            return $this;
        }
        if ($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            $address->setDiscountCouponAmount(0);
            $address->setBaseDiscountCouponAmount(0);
        }

        return $this;

    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->_getDiscountCondition($address)) {
            if ($address->getDiscountCouponAmount() < 0) {
                $address->addTotal([
                    'code'  => $this->getCode(),
                    'title' => Mage::helper('mercadopago')->__('Discount Mercado Pago'),
                    'value' => $address->getDiscountCouponAmount()
                ]);
            }
        }

        return $this;
    }

    protected function _getDiscountCondition($address)
    {
        $req = Mage::app()->getRequest()->getParam('total_amount');

        return (!empty($req) && $address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING);

    }
}