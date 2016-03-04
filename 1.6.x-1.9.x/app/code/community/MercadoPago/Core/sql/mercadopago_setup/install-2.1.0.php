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
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'finance_cost_amount', $options);
    $installer->addAttribute($entity, 'base_finance_cost_amount', $options);
}

$installer->endSetup();
