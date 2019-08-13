<?php
/**
 * fall back to create table if existing modules already exists to support upgrade
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Mysql4_Setup */

$installer->startSetup();

try {
    // Update Table
    $installer->run("
        ALTER TABLE `{$this->getTable('bronto_order_queue')}` ADD COLUMN `bronto_suppressed` VARCHAR(255) NULL DEFAULT NULL;
    ");
} catch (Exception $e) {
    throw new RuntimeException('Failed Modifying Table: ' . $e->getMessage());
}

$installer->endSetup();
