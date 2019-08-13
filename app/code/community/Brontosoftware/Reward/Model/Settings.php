<?php

class Brontosoftware_Reward_Model_Settings extends Brontosoftware_Magento_Reward_Settings
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_reward/manager'));
    }
}
