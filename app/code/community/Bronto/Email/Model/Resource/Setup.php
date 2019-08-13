<?php

class Bronto_Email_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * Sets the Bronto sending for all available scopes if the module is enabled
     *
     * @return Bronto_Email_Model_Resource_Setup
     */
    public function setDefaultSending()
    {
        $this->_reloadNewConfig()->_setDefaultSending()->_reloadNewConfig();
        foreach (Mage::app()->getWebsites() as $website) {
            $this->_setDefaultSending(null, $website->getId());
        }

        $this->_reloadNewConfig();
        foreach (Mage::app()->getStores() as $store) {
            $this->_setDefaultSending($store->getId());
        }

        return $this;
    }

    /**
     * @return Bronto_Email_Model_Resource_Setup
     */
    protected function _reloadNewConfig()
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        return $this;
    }

    /**
     * Sets the default sending to bronto is the module is enabled
     *
     * @param string|int $storeId
     * @param string|int $websiteId
     *
     * @return Bronto_Email_Model_Resource_Setup
     */
    protected function _setDefaultSending($storeId = null, $websiteId = null)
    {
        if (!is_null($storeId)) {
            $scope   = 'store';
            $scopeId = $storeId;
        } elseif (!is_null($websiteId)) {
            $scope   = 'website';
            $scopeId = $websiteId;
        } else {
            $scope   = 'default';
            $scopeId = 0;
        }
        $helper = Mage::helper('bronto_email');
        if (
            $helper->isEnabled($scope, $scopeId) &&
            !$helper->canUseBronto($scope, $scopeId)
        ) {
            $helper->setUseBronto(true, $scope, $scopeId);
        }

        return $this;
    }
}
