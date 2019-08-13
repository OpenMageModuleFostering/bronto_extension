<?php

class Brontosoftware_Product_Model_Observer extends Brontosoftware_Magento_Product_ExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        $settings = Mage::getSingleton('brontosoftware_product/settings');
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_productAttributeBridge'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_middleware'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_registration'),
            Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge'),
            Mage::getSingleton('brontosoftware_connector/impl_core_emulation'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_queue'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            $settings,
            Mage::getSingleton('brontosoftware_connector/impl_connector_platform'),
            new Brontosoftware_Magento_Product_Event_Source($settings));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_product')->__($message);
    }

    /**
     * @see parent
     */
    protected function _collection()
    {
        return Mage::getModel('catalog/product')->getCollection();
    }
}
