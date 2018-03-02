<?php

class MercadoPago_Core_Model_Discount_Coupon
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_code = 'discount_coupon';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {

        if ($this->_getDiscountCondition($address)) {
            parent::collect($address);

            //get data by request
            $postData = Mage::app()->getRequest()->getPost();
            $method = $postData['payment']['method'];
            $balance = $postData['payment'][$method]['discount'] * -1;


            $core = Mage::getModel('mercadopago/core');
            $coupon_id = $postData['payment'][$method]['coupon_code'];
            $response = $core->validCoupon($coupon_id);

            //set values in object
            $address->setDiscountCouponAmount($balance);
            $address->setBaseDiscountCouponAmount($balance);

            //set values in order detail
            $this->_setAmount($balance);
            $this->_setBaseAmount($balance);

            return $this;
        }

        if ($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            // set/reset values
            $address->setDiscountCouponAmount(0);
            $address->setBaseDiscountCouponAmount(0);
        }

        return $this;

    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {   

        if ($this->_getDiscountCondition($address)) {
            if ($address->getDiscountCouponAmount() < 0) {

                //add detail discount in list totals
                $address->addTotal(array(
                    'code'  => $this->getCode(),
                    'title' => Mage::helper('mercadopago')->__('Discount Mercado Pago'),
                    'value' => $address->getDiscountCouponAmount()
                ));
            }
        }

        return $this;
    }

  protected function _getDiscountCondition($address)
  {
    $postData = Mage::app()->getRequest()->getPost();

    if($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING && isset($postData['payment'])){
      $method = $postData['payment']['method'];
      if(isset($postData['payment'][$method]) && isset($postData['payment'][$method]['amount'])){
        $req = $postData['payment'][$method]['amount'];            
        return (!empty($req) && Mage::getStoreConfigFlag('payment/mercadopago/consider_discount'));
      }
    }
    return false;
  }
}
