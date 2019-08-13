<?php

class Brontosoftware_Inventory_Model_Observer extends Brontosoftware_Magento_Inventory_CatalogMapper
{
    /**
     * @see parent
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_product/observer'),
            Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge'),
            Mage::getModel('brontosoftware_inventory/manager'));
    }
}
