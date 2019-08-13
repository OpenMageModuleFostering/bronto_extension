<?php

class Brontosoftware_Notification_Model_Manager extends Brontosoftware_Magento_Notification_Manager
{
    /**
     * Override of DI
     */
    public function __construct() {
        parent::__construct(Mage::getModel('brontosoftware_connector/impl_core_inbox'));
    }
}
