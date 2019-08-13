<?php

class Brontosoftware_Email_Model_Redirect extends Brontosoftware_Magento_Email_Redirector
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getModel('brontosoftware_connector/impl_core_store'),
            Mage::getModel('brontosoftware_connector/impl_core_customerSession'),
            Mage::getModel('brontosoftware_connector/impl_core_quoteManagement'),
            Mage::getModel('brontosoftware_connector/impl_core_checkoutSession'),
            Mage::getModel('brontosoftware_connector/impl_core_encryptor'),
            Mage::getModel('brontosoftware_email/settings'),
            Mage::getModel('brontosoftware_connector/impl_core_logger'));
    }
}
