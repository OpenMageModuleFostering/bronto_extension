<?php

class Brontosoftware_Product_Model_Settings extends Brontosoftware_Magento_Product_Settings
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_scoped'),
            Mage::getSingleton('brontosoftware_connector/impl_core_config'),
            Mage::getSingleton('brontosoftware_connector/impl_core_event'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge'));
    }
}
