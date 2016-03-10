<?php

class MercadoPago_Core_Model_Custom_Finance_Cost_Creditmemo
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    protected $_code = 'financing_cost';

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $amount = $order->getFinanceCostAmount();
        $baseAmount = $order->getBaseFinanceCostAmount();
        if ($amount) {
            $creditmemo->setFinanceCostAmount($amount);
            $creditmemo->setBaseFinanceCostAmount($baseAmount);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount);
        }

        return $this;
    }

}