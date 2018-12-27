<?php

class MercadoPago_MercadoEnvios_Helper_ItemData
    extends Mage_Core_Helper_Abstract
{
    public function itemHasChildren($item)
    {
        $children = $item->getChildren();

        return (!empty($children) || (get_class($item) == 'Mage_Sales_Model_Order_Item' && $item->getHasChildren()));
    }

    public function itemGetQty($item) {
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }
        $qty = (get_class($item) == 'Mage_Sales_Model_Quote_Item') ? $item->getQty() : $item->getQtyOrdered();
        return $qty;
    }
}