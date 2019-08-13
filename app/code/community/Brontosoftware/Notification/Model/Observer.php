<?php

class Brontosoftware_Notification_Model_Observer extends Brontosoftware_Magento_Notification_ExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getModel('brontosoftware_connector/settings'),
            Mage::getModel('brontosoftware_notification/settings'),
            Mage::getModel('brontosoftware_notification/manager'));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_notification')->__($message);
    }
}
