<?php

class Brontosoftware_Recommendation_Model_Observer extends Brontosoftware_Magento_Recommendation_ExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getModel('brontosoftware_connector/impl_core_categoryCacheBridge'),
            Mage::getModel('brontosoftware_connector/impl_core_productCacheBridge'),
            Mage::getModel('brontosoftware_recommendation/impl_reports'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_middleware'),
            Mage::getModel('brontosoftware_recommendation/settings'),
            Mage::getModel('brontosoftware_recommendation/context_factory'),
            Mage::getModel('brontosoftware_connector/impl_core_emulation'),
            Mage::getModel('brontosoftware_connector/impl_core_logger'));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_recommendation')->__($message);
    }
}
