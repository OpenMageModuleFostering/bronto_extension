<?php

class Brontosoftware_Optin_Model_Checkout extends Brontosoftware_Magento_Optin_Checkout
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_optin/settings'),
            Mage::getSingleton('brontosoftware_connector/impl_core_subscriber'));
    }
}
