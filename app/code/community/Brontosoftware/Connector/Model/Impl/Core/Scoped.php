<?php

class Brontosoftware_Connector_Model_Impl_Core_Scoped implements Brontosoftware_Magento_Core_Config_ScopedInterface
{
    /**
     * @see parent
     */
    public function getValue($path, $scopeName = 'default', $scopeId = null)
    {
        $store = Mage::app()->getStore($scopeId);
        return $store->getConfig($path);
    }

    /**
     * @see parent
     */
    public function isSetFlag($path, $scopeName = 'default', $scopeId = null)
    {
        $store = Mage::app()->getStore($scopeId);
        return (bool)$store->getConfig($path);
    }
}
