<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Model_Keysentry extends Mage_Core_Model_Abstract
{
    /**
     * Bronto email module alias
     */
    const EMAIL      = 'bronto_email';

    /**
     * Bronto order module alias
     */
    const ORDER      = 'bronto_order';

    /**
     * Bronto reminder module alias
     */
    const REMINDER   = 'bronto_reminder';

    /**
     * Bronto newsletter module alias
     */
    const NEWSLETTER = 'bronto_newsletter';

    /**
     * Disable all the defined modules for the scope
     *
     * @param mixed   $scope   Site scope
     * @param integer $scopeId Site scope id
     */
    public function disableModules($scope, $scopeId)
    {
        Mage::helper(self::EMAIL)->disableModule($scope, $scopeId);
        Mage::helper(self::ORDER)->disableModule($scope, $scopeId);
        Mage::helper(self::REMINDER)->disableModule($scope, $scopeId);
        Mage::helper(self::NEWSLETTER)->disableModule($scope, $scopeId);
    }

    public function unlinkEmails(Mage_Core_Model_Resource_Db_Collection_Abstract $collection, $scopeId)
    {
/*
        //  missing key variable.  disabling for launch, keys are still relinked later
        Mage::log($scopeId);
        $collection->addFieldToFilter('bronto_message_id', array('eq' => strtolower($apiKey)));

        // foreach ($collection as $template) {
        //     $template->unsetData('bronto_message_id');
        //     $template->save();
        // }
        //  I think this is wrong (JK)
        //  @TODO comeback and revaluate this code
        Mage::log($scopeId);
        $collection->addFieldToFilter('bronto_message_id', array('eq' => strtolower($apiKey)));

        foreach ($collection as $template) {
            $template->unsetData('bronto_message_id');
            $template->save();
        }
*/
    }
}
