<?php

$installer = $this;
$installer->startSetup();
/**
 * Add 'custom_attribute' attribute for entities
 */
$entities = array(
    'quote_address',
    'order',
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'size'     => '12,4',
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'discount_coupon_amount', $options);
    $installer->addAttribute($entity, 'base_discount_coupon_amount', $options);
}

$installer->endSetup();
