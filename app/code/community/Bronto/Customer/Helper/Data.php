<?php

/**
 * @package   Bronto\Customer
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.0.0
 */
class Bronto_Customer_Helper_Data extends Bronto_Common_Helper_Data implements Bronto_Common_Helper_DataInterface
{
    const XML_PATH_ENABLED      = 'bronto_customer/settings/enabled';
    const XML_PATH_LIMIT        = 'bronto_customer/settings/limit';
    const XML_PATH_INSTALL_DATE = 'bronto_customer/settings/install_date';
    const XML_PATH_UPGRADE_DATE = 'bronto_customer/settings/upgrade_date';

    const XML_PREFIX_CUSTOMER_ATTR = 'bronto_customer/attributes/';
    const XML_PREFIX_ADDRESS_ATTR  = 'bronto_customer/address_attributes/';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @param  string $attribute
     * @param  int|string $store
     * @return mixed
     */
    public function getCustomerAttributeField($attribute, $store = null)
    {
        return Mage::getStoreConfig(self::XML_PREFIX_CUSTOMER_ATTR .  $attribute, $store);
    }

    /**
     * @param  string $attribute
     * @param  int|string $store
     * @return mixed
     */
    public function getAddressAttributeField($attribute, $store = null)
    {
        return Mage::getStoreConfig(self::XML_PREFIX_ADDRESS_ATTR .  $attribute, $store);
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
     * Retrieve helper module name
     * @return string
     */
    protected function _getModuleName()
    {
        return 'bronto_customer';
    }
}
