<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Helper_Contact extends Bronto_Common_Helper_Contact
{
    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        return 'Bronto_Email';
    }

    /**
     * @param string $email
     * @return Bronto_Api_Contact_Row
     */
    public function getContactByEmail($email, $customSource = 'bronto_email', $store = null)
    {
        return parent::getContactByEmail($email, $customSource, $store);
    }
}
