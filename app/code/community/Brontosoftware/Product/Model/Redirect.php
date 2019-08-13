<?php

class Brontosoftware_Product_Model_Redirect extends Brontosoftware_Magento_Product_Redirector
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getModel('brontosoftware_connector/impl_core_store'),
            Mage::getModel('brontosoftware_connector/impl_core_checkoutSession'),
            Mage::getModel('brontosoftware_connector/impl_core_productCacheBridge'),
            Mage::getModel('brontosoftware_product/settings'),
            Mage::getModel('brontosoftware_connector/impl_core_logger'));
    }
}
