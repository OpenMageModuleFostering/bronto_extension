<?php

class Brontosoftware_Order_Model_Observer extends Brontosoftware_Magento_Order_ExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        $settings = Mage::getSingleton('brontosoftware_order/settings');
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_productAttributeBridge'),
            Mage::getSingleton('brontosoftware_connector/impl_core_orderStatuses'),
            Mage::getSingleton('brontosoftware_connector/impl_core_orderCacheBridge'),
            Mage::getSingleton('brontosoftware_connector/impl_core_emulation'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_queue'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            $settings,
            Mage::getSingleton('brontosoftware_connector/impl_connector_platform'),
            new Brontosoftware_Magento_Order_Event_Source(
                Mage::getSingleton('brontosoftware_connector/settings'),
                $settings,
                Mage::getSingleton('brontosoftware_connector/impl_core_cookies')));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_order')->__($message);
    }

    /**
     * @see parent
     */
    protected function _collection()
    {
        return Mage::getModel('sales/order')->getCollection();
    }
}
