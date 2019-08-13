<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Model_Keysentry extends Mage_Core_Model_Abstract
{
    /**
     * Bronto customer module alias
     */
    const CUSTOMER = 'bronto_customer';

    /**
     * Bronto email module alias
     */
    const EMAIL = 'bronto_email';

    /**
     * Bronto newsletter module alias
     */
    const NEWS = 'bronto_news';

    /**
     * Bronto newsletter module alias
     */
    const NEWSLETTER = 'bronto_newsletter';

    /**
     * Bronto order module alias
     */
    const ORDER = 'bronto_order';

    /**
     * Bronto reminder module alias
     */
    const REMINDER = 'bronto_reminder';

    /**
     * Disable all the defined modules for the scope
     *
     * @param mixed $scope   Site scope
     * @param integer $scopeId Site scope id
     */
    public function disableModules($scope, $scopeId)
    {
        Mage::helper(self::CUSTOMER)->disableModule($scope, $scopeId);
        Mage::helper(self::EMAIL)->disableModule($scope, $scopeId);
        Mage::helper(self::NEWS)->disableModule($scope, $scopeId);
        Mage::helper(self::NEWSLETTER)->disableModule($scope, $scopeId);
        Mage::helper(self::ORDER)->disableModule($scope, $scopeId);
        Mage::helper(self::REMINDER)->disableModule($scope, $scopeId);

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }

    /**
     * Remove Bronto Message Connection for Template
     *
     * @param Varien_Data_Collection_Db $collection
     * @param $scopeId Store ID
     */
    public function unlinkEmails(Varien_Data_Collection_Db $collection, $scope, $scopeId)
    {
        switch ($scope) {
            case 'stores':
            case 'store':
                $storeId = $scopeId;
                break;
            case 'websites':
            case 'website':
                $storeId = Mage::app()->getWebsite($scopeId)->getStoreIds();
                break;
            default:
                $storeId = false;
                break;
        }

        // create filter
        if ($storeId) {
            if (is_array($storeId)) {
                $filter = array('in' => $storeId);
            } else {
                $filter = array('eq' => $storeId);
            }
            $collection->addFieldToFilter('store_id', $filter);
        }

        // Delete Bronto Message connection to template
        foreach ($collection as $message) {
            $message->delete();
        }
    }
}
