<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.5
 */
class Bronto_Order_Helper_Data extends Bronto_Common_Helper_Data implements Bronto_Common_Helper_DataInterface
{
    const XML_PATH_ENABLED      = 'bronto_order/settings/enabled';
    const XML_PATH_LIMIT        = 'bronto_order/settings/limit';
    const XML_PATH_DESCRIPTION  = 'bronto_order/settings/description_attribute';
    const XML_PATH_INSTALL_DATE = 'bronto_order/settings/install_date';
    const XML_PATH_UPGRADE_DATE = 'bronto_order/settings/upgrade_date';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function disableModule($scope = 'default', $scopeId = 0)
    {
        return $this->_disableModule(self::XML_PATH_ENABLED, $scope, $scopeId);
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return (int) Mage::getStoreConfig(self::XML_PATH_LIMIT);
    }

    /**
     * @return string
     */
    public function getDescriptionAttribute()
    {
        return Mage::getStoreConfig(self::XML_PATH_DESCRIPTION);
    }

    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        return 'Bronto_Order';
    }
}
