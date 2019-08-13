<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Model_Observer
{
    const NOTICE_IDENTIFER = 'bronto_email';

    /**
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function checkBrontoRequirements(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return;
        }

        // Verify Requirements
        if (!Mage::helper(self::NOTICE_IDENTIFER)->varifyRequirements(self::NOTICE_IDENTIFER, array('soap', 'openssl'))) {
            return;
        }
    }

    /**
     * Observes module becoming enabled and displays message warning user to configure settings
     * @param Varien_Event_Observer $observer
     */
    public function watchEnableAction(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('bronto_email')->__(Mage::helper('bronto_email')->getModuleEnabledText()));
    }

    /**
     * Grab Config Data Object before save and handle the 'Create New...' value for
     * fields that were generated dynamically
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    public function saveDynamicField(Varien_Event_Observer $observer)
    {
        if (!Mage::helper(self::NOTICE_IDENTIFER)->isEnabled()) {
            return $observer;
        }

        $action = $observer->getEvent()->getControllerAction();

        if ($action->getRequest()->getParam('section') == 'bronto_email') {
            $groups  = $action->getRequest()->getPost('groups');
            $website = $action->getRequest()->getParam('website');
            $store   = $action->getRequest()->getParam('store');

            // Handle saving as real path values
            $this->_handleAttributes($website, $store, $groups);

            // Unset groups for bronto_email template paths
            unset($groups['templates']);
            $observer->getEvent()->getControllerAction()
                ->getRequest()->setPost('groups', $groups);

            // reinit configuration
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();
        }

        return $observer;
    }

    /**
     * Get Section, Group, and field from field path and save to "real" path
     * @param type $website
     * @param type $store
     * @param type $groups
     */
    protected function _handleAttributes($website, $store, $groups)
    {
        $fields      = $groups['templates']['fields'];
        $config      = Mage::getModel('core/config');
        $scopeParams = Mage::helper('bronto_common')->getScopeParams();

        // Cycle through template fields
        foreach ($fields as $field => $fieldData) {
            // Get Section, Group and Field
            list($sectionName, $groupName, $fieldName) = explode('-', $field);
            $sectionName = str_replace('bronto_email_templates_', '', $sectionName);
            if (is_array($fieldData) && !array_key_exists('value', $fieldData)) {
                continue;
            }

            // Because send_type values aren't arrays, we have to 
            $value = (is_array($fieldData)) ? $fieldData['value'] : $fieldData;

            $scope = $scopeParams['scope'];
            if ($scope != 'default') {
                $scope .= 's';
            }

            $config->saveConfig(
                $sectionName.'/'.$groupName.'/'.$fieldName,
                $value,
                $scope,
                $scopeParams[$scopeParams['scope'].'_id']
            );
        }
    }
}
