<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_Mysql4_Guest extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('bronto_reminder/guest', 'guest_email_id');
    }
}
