<?php

class Brontosoftware_Connector_Model_Impl_Core_Store implements Brontosoftware_Magento_Core_Store_ManagerInterface
{
    /**
     * @see parent
     */
    public function getStore($storeId = null)
    {
        return Mage::app()->getStore($storeId);
    }

    /**
     * @see parent
     */
    public function getStores()
    {
        return Mage::app()->getStores();
    }

    /**
     * @see parent
     */
    public function getDefaultStoreView()
    {
        return Mage::app()->getDefaultStoreView();
    }

    /**
     * @see parent
     */
    public function getWebsite($websiteId = null)
    {
        return Mage::app()->getWebsite($websiteId);
    }

    /**
     * @see parent
     */
    public function getWebsites()
    {
        return Mage::app()->getWebsites();
    }

    /**
     * @see parent
     */
    public function reinitStores()
    {
        Mage::app()->reinitStores();
    }
}
