<?php

/**
 * @package   Bronto\Customer
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.0.0
 */
class Bronto_Customer_Model_Customer_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function markCustomerForReimport(Varien_Event_Observer $observer)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getCustomer();
        $customer->setBrontoImported(null);
    }
}
