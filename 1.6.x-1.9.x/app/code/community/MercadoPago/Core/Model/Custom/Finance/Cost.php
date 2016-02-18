<?php

class MercadoPago_Core_Model_Custom_Finance_Cost
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_code = 'financing_cost';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->_getFinancingCondition($address)) {

            $postData = Mage::app()->getRequest()->getPost();
            parent::collect($address);

            $totalAmount = (float)$postData['total_amount'];
            $amount = (float)$postData['amount'] - (float)$postData['mercadopago-discount-amount'];
            $balance = $totalAmount - $amount;

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
        $req = Mage::app()->getRequest()->getParam('total_amount');

        return (!empty($req) && $address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING);

    }
}