<?php

class Brontosoftware_Email_Model_Trigger_Observer extends Brontosoftware_Magento_Email_Trigger_Observer
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_email/impl_trigger'),
            Mage::getSingleton('brontosoftware_email/settings'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge'),
            Mage::getSingleton('brontosoftware_connector/impl_core_customer'),
            Mage::getSingleton('brontosoftware_integration/settings'));
    }
}
