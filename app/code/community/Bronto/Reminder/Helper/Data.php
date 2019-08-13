<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Helper_Data extends Bronto_Common_Helper_Data implements Bronto_Common_Helper_DataInterface
{
    const XML_PATH_ENABLED         = 'bronto_reminder/settings/enabled';
    const XML_PATH_INTERVAL        = 'bronto_reminder/settings/interval';
    const XML_PATH_SEND_LIMIT      = 'bronto_reminder/settings/limit';
    const XML_PATH_EMAIL_IDENTITY  = 'bronto_reminder/settings/identity';
    const XML_PATH_EMAIL_THRESHOLD = 'bronto_reminder/settings/threshold';

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
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function disableModule($scope = 'default', $scopeId = 0)
    {
        return $this->_disableModule(self::XML_PATH_ENABLED, $scope, $scopeId);
    }

    /**
     * @return int
     */
    public function getCronInterval()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_INTERVAL);
    }

    /**
     * @return int
     */
    public function getOneRunLimit()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_SEND_LIMIT);
    }

    /**
     * @return string
     */
    public function getEmailIdentity()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY);
    }

    /**
     * @return int
     */
    public function getSendFailureThreshold()
    {
        if (Mage::helper('bronto_common')->isTestModeEnabled()) {
            return 0;
        }

        return (int) Mage::getStoreConfig(self::XML_PATH_EMAIL_THRESHOLD);
    }
}
