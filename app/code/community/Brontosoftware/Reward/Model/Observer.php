<?php

class Brontosoftware_Reward_Model_Observer extends Brontosoftware_Magento_Contact_AttributeExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(Mage::getModel('brontosoftware_reward/settings'));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_reward')->__($message);
    }
}
