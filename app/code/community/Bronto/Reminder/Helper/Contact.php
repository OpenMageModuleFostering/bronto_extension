<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Helper_Contact extends Bronto_Common_Helper_Contact
{
    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        return 'Bronto_Reminder';
    }

    /**
     * @param string $email
     * @return Bronto_Api_Contact_Row
     */
    public function getContactByEmail($email, $customSource = 'bronto_reminder', $store = null)
    {
        return parent::getContactByEmail($email, $customSource, $store);
    }
}
