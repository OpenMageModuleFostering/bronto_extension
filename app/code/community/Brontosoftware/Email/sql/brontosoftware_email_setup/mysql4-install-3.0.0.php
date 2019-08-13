<?php

$installer = $this;
$installer->startSetup();
$triggers = $installer->getTable('brontosoftware_email/trigger');
try {
    $installer->run("DROP TABLE IF EXISTS `{$triggers}`;");
    $installer->run("
    CREATE TABLE `{$triggers}` (
        `trigger_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `site_id` varchar(120) NOT NULL,
        `store_id` varchar(11) NOT NULL,
        `message_id` varchar(36) NOT NULL,
        `message_type` varchar(36) NOT NULL,
        `model_type` varchar(32) NOT NULL,
        `model_id` int(11) unsigned NOT NULL,
        `customer_email` varchar(255) NOT NULL,
        `sent_message` smallint(1) unsigned NOT NULL DEFAULT '0',
        `triggered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`trigger_id`),
        KEY `IDX_BRONTO_TRIGGER_MODEL` (`site_id`, `model_type`, `model_id`),
        KEY `IDX_BRONTO_CUSTOMER_EMAIL` (`site_id`, `sent_message`, `customer_email`),
        KEY `IDX_BRONTO_TRIGGER_APPLICABLE` (`site_id`, `sent_message`, `triggered_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
} catch (Exception $e) {
    Mage::log("Failed to create {$triggers}: {$e->getMessage()}", Zend_Log::ERR, 'brontosoftware_connector.log', true);
}
$installer->endSetup();
