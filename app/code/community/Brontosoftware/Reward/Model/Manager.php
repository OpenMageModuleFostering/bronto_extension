<?php

class Brontosoftware_Reward_Model_Manager implements Brontosoftware_Magento_Reward_ManagerInterface
{
    /**
     * @see parent
     */
    public function getByCustomer($customerId, $websiteId)
    {
        $reward = Mage::getModel('enterprise_reward/reward');
        if (empty($reward)) {
            return null;
        }
        $reward->setCustomerId($customerId)
            ->setWebsiteId($websiteId)
            ->loadByCustomer();
        if ($reward->getId()) {
            return $reward;
        }
        return null;
    }
}
