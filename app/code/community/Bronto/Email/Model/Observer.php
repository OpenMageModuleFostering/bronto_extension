<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Model_Observer
{
    const NOTICE_IDENTIFER = 'bronto_email';

    /**
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function checkBrontoRequirements(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return;
        }

        // Verify Requirements
        if (!Mage::helper(self::NOTICE_IDENTIFER)->varifyRequirements(self::NOTICE_IDENTIFER, array('soap', 'openssl'))) {
            return;
        }
    }
}
