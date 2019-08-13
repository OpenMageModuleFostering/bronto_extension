<?php

class Brontosoftware_Browse_Model_Settings extends Brontosoftware_Magento_Browse_Settings
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_cookies'),
            Mage::getSingleton('brontosoftware_connector/impl_core_cookies'),
            Mage::getSingleton('brontosoftware_integration/settings'),
            Mage::getSingleton('brontosoftware_connector/impl_core_customerSession'),
            Mage::getSingleton('brontosoftware_connector/impl_core_scoped'),
            Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge'));
    }
}
