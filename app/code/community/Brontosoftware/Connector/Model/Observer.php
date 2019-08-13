<?php

class Brontosoftware_Connector_Model_Observer extends Brontosoftware_Magento_Advanced_ExtensionAbstract
{
    /**
     * Override because Mage 1 doesn't support DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_config'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_middleware'),
            Mage::getModel('brontosoftware_connector/impl_core_event'),
            Mage::getModel('brontosoftware_connector/impl_connector_queue'),
            Mage::getModel('brontosoftware_connector/impl_connector_platform'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            Mage::getSingleton('brontosoftware_connector/impl_core_emulation'));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_connector')->__($message);
    }
}
