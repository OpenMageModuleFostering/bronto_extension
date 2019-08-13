<?php

class Brontosoftware_Balance_Model_Manager implements Brontosoftware_Magento_Balance_ManagerInterface
{
    /**
     * @see parent
     */
    public function getByCustomer($customerId, $websiteId)
    {
        $credit = Mage::getModel('enterprise_customerbalance/balance');
        if (empty($credit)) {
            return null;
        }
        $credit->setCustomerId($customerId)
            ->setWebsiteId($websiteId)
            ->loadByCustomer();
        if ($credit->getId()) {
            return $credit;
        }
        return null;
    }
}
