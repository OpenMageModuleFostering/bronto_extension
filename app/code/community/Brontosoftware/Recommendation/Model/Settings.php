<?php

class Brontosoftware_Recommendation_Model_Settings extends Brontosoftware_Magento_Recommendation_Settings
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getModel('brontosoftware_connector/impl_core_config'),
            Mage::getModel('brontosoftware_connector/impl_core_scoped'),
            Mage::getModel('brontosoftware_recommendation/impl_reports'),
            Mage::getModel('brontosoftware_recommendation/source_factory'),
            Mage::getModel('brontosoftware_connector/impl_core_productCacheBridge'),
            Mage::getModel('brontosoftware_connector/impl_core_store'),
            Mage::getModel('brontosoftware_connector/impl_core_event'),
            Mage::getModel('brontosoftware_connector/impl_core_customer'));
    }
}
