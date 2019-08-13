<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_Email_Message extends Bronto_Common_Model_Email_Template
{
    /**
     * @var string
     */
    protected $_helper = 'bronto_reminder';

    /**
     * @var string
     */
    protected $_apiLogFile = 'bronto_reminder_api.log';

    /**
     * Log the Delivery API call
     *
     * @param boolean $success
     * @param string $error (Optional)
     * @param Bronto_Api_Delivery_Row $delivery (Optional)
     */
    protected function _afterSend($success, $error = null, Bronto_Api_Delivery_Row $delivery = null)
    {
        if (!is_null($delivery)) {
            $helper = Mage::helper($this->_helper);
            $status = $success ? "Successful" : "Failed";

            $helper->writeVerboseDebug("===== $status Reminder Delivery =====", $this->_apiLogFile);
            $helper->writeVerboseDebug(var_export($delivery->getApi()->getLastRequest(), true), $this->_apiLogFile);
            $helper->writeVerboseDebug(var_export($delivery->getApi()->getLastResponse(), true), $this->_apiLogFile);
        }
    }
}
