<?php

/**
 * @category Bronto
 * @package Common
 */
class Bronto_Common_Model_Observer
{

    /**
     * Description for const
     */
    const NOTICE_IDENTIFER = 'bronto_common';

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

        // Verify API tokens are valid
        if (Mage::helper(self::NOTICE_IDENTIFER)->isEnabled() && !Mage::helper(self::NOTICE_IDENTIFER)->validApiTokens(self::NOTICE_IDENTIFER)) {
            return false;
        }
    }
}
