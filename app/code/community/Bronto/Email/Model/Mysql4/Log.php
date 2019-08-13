<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('bronto_email/log', 'log_id');
    }
}
