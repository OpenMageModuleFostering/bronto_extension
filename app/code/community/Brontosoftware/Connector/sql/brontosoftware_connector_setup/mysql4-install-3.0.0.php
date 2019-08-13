<?php

$installer = $this;
$installer->startSetup();
$registrations = $installer->getTable('brontosoftware_connector/registration');
try {
    $installer->run("DROP TABLE IF EXISTS `{$registrations}`;");
    $installer->run("
    CREATE TABLE `{$registrations}` (
        `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL DEFAULT 'Platform Registration',
        `environment` varchar(50) NOT NULL DEFAULT 'Development',
        `connector_key` varchar(255) NOT NULL,
        `scope` varchar(8) NOT NULL DEFAULT 'default',
        `scope_id` int(11) unsigned NOT NULL DEFAULT 0,
        `scope_code` varchar(32) NOT NULL DEFAULT '',
        `is_active` smallint(1) unsigned NOT NULL DEFAULT 0,
        `is_protected` smallint(1) unsigned NOT NULL DEFAULT 0,
        `username` varchar(255) DEFAULT '',
        `password` varchar(255) DEFAULT '',
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`entity_id`),
        KEY `IDX_BRONTO_REGISTRATION_ADD` (`is_active`),
        KEY `IDX_BRONTO_ENVIRONMENT` (`environment`),
        UNIQUE KEY `UNQ_BRONTO_SCOPE_SCOPEID` (`scope`, `scope_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
} catch (Exception $e) {
    Mage::log("Failed to create {$registrations}: {$e->getMessage()}", Zend_Log::ERR, 'brontosoftware_connector.log', true);
}

$queue = $installer->getTable('brontosoftware_connector/queue');
try {
    $installer->run("DROP TABLE IF EXISTS `{$queue}`;");
    $installer->run("
    CREATE TABLE `{$queue}` (
        `queue_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `site_id` varchar(120) NOT NULL,
        `event_type` varchar(32) NOT NULL,
        `event_data` text NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`queue_id`),
        KEY `IDX_BRONTO_SCRIPT_CREATED` (`site_id`, `created_at`),
        KEY `IDX_BRONTO_SCRIPT_EVENT_TYPE` (`site_id`, `event_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
} catch (Exception $e) {
    Mage::log("Failed to create {$queue}: {$e->getMessage()}", Zend_Log::ERR, 'brontosoftware_connector.log', true);
}

$installer->endSetup();
