<?php

class Brontosoftware_Coupon_Model_Manager extends Brontosoftware_Magento_Coupon_Manager
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getModel('brontosoftware_connector/impl_core_scoped'),
            Mage::getModel('brontosoftware_connector/impl_core_config'),
            Mage::getModel('brontosoftware_connector/impl_core_config'),
            Mage::getModel('brontosoftware_connector/impl_core_rules'));
    }
}
