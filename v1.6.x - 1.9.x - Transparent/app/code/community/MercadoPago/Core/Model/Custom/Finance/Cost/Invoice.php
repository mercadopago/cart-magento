<?php

class MercadoPago_Core_Model_Custom_Finance_Cost_Invoice
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    protected $_code = 'financing_cost';

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $amount = $order->getFinanceCostAmount();
        $baseAmount = $order->getBaseFinanceCostAmount();
        if ($amount) {
            $invoice->setFinanceCostAmount($amount);
            $invoice->setBaseFinanceCostAmount($baseAmount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseAmount);
        }

        return $this;
    }

}