<?php

class MercadoPago_Core_Block_Sales_Order_Totals_Finance_Cost
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
        if ((float)$this->getSource()->getFinanceCostAmount() == 0 || !Mage::getStoreConfigFlag('payment/mercadopago/financing_cost')) {
            return $this;
        }
        $total = new Varien_Object(array(
            'code'  => 'financing_cost',
            'field' => 'financing_cost_amount',
            'value' => $this->getSource()->getFinanceCostAmount(),
            'label' => $this->__('Financing Cost'),
        ));
        $this->getParentBlock()->addTotalBefore($total, 'shipping');

        return $this;
    }
}
