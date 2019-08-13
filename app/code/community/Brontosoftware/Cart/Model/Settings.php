<?php

class Brontosoftware_Cart_Model_Settings extends Brontosoftware_Magento_Cart_Settings
{
    /**
     * @Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_cookies'),
            Mage::getModel('brontosoftware_connector/impl_core_encryptor'),
            Mage::getSingleton('brontosoftware_connector/impl_core_cookies'),
            Mage::getSingleton('brontosoftware_connector/impl_core_scoped'),
            Mage::getSingleton('brontosoftware_connector/impl_core_urls'));
    }
}
