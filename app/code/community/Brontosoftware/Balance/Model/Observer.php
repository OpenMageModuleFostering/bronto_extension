<?php

class Brontosoftware_Balance_Model_Observer extends Brontosoftware_Magento_Contact_AttributeExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(Mage::getModel('brontosoftware_balance/settings'));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_balance')->__($message);
    }
}
