<?php

class MercadoPago_Core_Model_Custom_Finance_Cost
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_code = 'financing_cost';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->_getFinancingCondition($address)) {

            $postData = Mage::app()->getRequest()->getPost();
            $method = $postData['payment']['method'];
            parent::collect($address);

            $totalAmount  = (float) $postData['payment'][$method]['total_amount'];
            $amount       = (float) $postData['payment'][$method]['amount'];
            $discount     = (float) $postData['payment'][$method]['discount'];
          
            $real_amount = $amount - $discount;
            $balance = $totalAmount - $real_amount;

            $address->setFinanceCostAmount($balance);
            $address->setBaseFinanceCostAmount($balance);

            $this->_setAmount($balance);
            $this->_setBaseAmount($balance);

            return $this;
        }

        if ($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            $address->setFinanceCostAmount(0);
            $address->setBaseFinanceCostAmount(0);
        }

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->_getFinancingCondition($address)) {
          if ($address->getFinanceCostAmount() > 0) {

                $address->addTotal(array(
                    'code'  => $this->getCode(),
                    'title' => Mage::helper('mercadopago')->__('Financing Cost'),
                    'value' => $address->getFinanceCostAmount()
                ));
            }
        }

        return $this;
    }

    protected function _getFinancingCondition($address)
    {
      $postData = Mage::app()->getRequest()->getPost();

      if($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING && isset($postData['payment'])){
        $method = $postData['payment']['method'];
        
        if(isset($postData['payment'][$method]) && isset($postData['payment'][$method]['amount']) ){
          $req = $postData['payment'][$method]['amount'];

          return (!empty($req) && Mage::getStoreConfigFlag('payment/mercadopago/financing_cost'));          
        }
      }
      return false;
    }
}