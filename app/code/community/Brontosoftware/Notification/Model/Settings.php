<?php

class Brontosoftware_Notification_Model_Settings extends Brontosoftware_Magento_Notification_Settings
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(Mage::getSingleton('brontosoftware_connector/impl_core_scoped'));
    }
}
