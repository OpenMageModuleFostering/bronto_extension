<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Helper_Data extends Bronto_Common_Helper_Data implements Bronto_Common_Helper_DataInterface
{
    const XML_PATH_ENABLED            = 'bronto_email/settings/enabled';
    const XML_PATH_LOG_ENABLED        = 'bronto_email/settings/log_enabled';
    const XML_PATH_LOG_FIELDS_ENABLED = 'bronto_email/settings/log_fields_enabled';

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
     * Disable the module in the admin configuration
     *
     * @return bool
     */
    public function disableModule($scope = 'default', $scopeId = 0)
    {
        return $this->_disableModule(self::XML_PATH_ENABLED, $scope, $scopeId);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @return bool
     */
    public function isLogEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_LOG_ENABLED);
    }

    /**
     * @return bool
     */
    public function isLogFieldsEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_LOG_FIELDS_ENABLED);
    }
}
