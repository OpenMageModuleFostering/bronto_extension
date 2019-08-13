<?php

/**
 * @category Bronto
 * @package  Common
 */
class Bronto_Common_Model_Observer
{

    private $_validatedFields = array(
        'site_name' => 'Bronto Site Name',
        'firstname' => 'First Name',
        'lastname'  => 'Last Name',
        'number'    => 'Phone Number',
        'email'     => 'Email',
        'title'     => 'Job Title',
    );

    /**
     * Description for const
     */
    const NOTICE_IDENTIFIER = 'bronto_common';

    const SUPPORT_IDENTIFIER = 'bronto_common/support';

    /**
     * Watches for the enable switch to change to disable
     *
     * event: bronto_disable
     *
     * @param Varien_Event_Observer $observer
     */
    public function watchDisableAction(Varien_Event_Observer $observer)
    {
        // Get Scope
        $scopeParams = Mage::helper('bronto_common')->getScopeParams();
        $scope       = $scopeParams['scope'];
        $scopeId     = $scopeParams[$scopeParams['scope'] . '_id'];

        // Get Sentry and Disable Modules
        $sentry = Mage::getModel('bronto_common/keysentry');
        $sentry->disableModules($scope, $scopeId, true);

        // Unlink all Emails
        if (!Mage::helper('bronto_common')->isVersionMatch(Mage::getVersionInfo(), 1, 9)) {
            $sentry->unlinkEmails(
                Mage::getModel('bronto_email/message')->getCollection(),
                $scope,
                $scopeId
            );
        }
    }

    /**
     * events: controller_action_predispatch
     *
     * @param Varien_Event_Observer $observer
     *
     * @return mixed
     */
    public function checkBrontoRequirements(Varien_Event_Observer $observer)
    {

        $action = $observer->getEvent()->getControllerAction();
        // In session, not Ajax, not POST
        if (
            !Mage::getSingleton('admin/session')->isLoggedIn() ||
            $action->getRequest()->isAjax() ||
            $action->getRequest()->isPost()
        ) {
            return false;
        }

        $helper = Mage::helper(self::NOTICE_IDENTIFIER);

        // Verify Requirements
        if (!$helper->varifyRequirements(self::NOTICE_IDENTIFIER, array('soap', 'openssl'))) {
            return false;
        }

        // Bug user about registration, only once
        $onBronto = $action->getRequest()->getParam('section') == 'bronto';
        !Mage::helper(self::SUPPORT_IDENTIFIER)->verifyRegistration($onBronto);

        // Verify API tokens are valid
        if ($helper->isEnabled() && !$helper->validApiStatus()) {
            return false;
        }

        return $this;
    }

    /**
     * Cron to clear downloaded zips
     */
    public function clearArchives($cron)
    {
        Mage::helper(self::SUPPORT_IDENTIFIER)->clearArchiveDirectory();
    }

    /**
     * Cron to clear really old log entries
     */
    public function clearOldLogs($cron)
    {
        $helper = Mage::helper(self::SUPPORT_IDENTIFIER);
        if ($helper->isEnabled() && $helper->shouldClearLogs()) {
            $helper->clearOldLogs();
        }
    }

    /**
     * Cron to process API errors
     */
    public function processApiErrors($cron = null)
    {
        $results = array(
          'total' => 0,
          'success' => 0,
          'error' => 0
        );

        $helper = Mage::helper('bronto_common/api');
        if (!$helper->isEnabled()) {
            return $results;
        }
        $helper->writeDebug('Retrying API errors.');
        $api = $helper->getApi();
        try {
            $api->login();
        } catch (Exception $e) {
            $helper->writeDebug('Skipping process because API is not taking calls: ' . $e->getMessage());
            return $results;
        }

        $collection = Mage::getModel('bronto_common/error')->getCollection()
            ->orderByOldest()
            ->addAttemptThreshold($helper->getAttemptThreshold())
            ->setPageSize($helper->getErrorThreshold());

        foreach ($collection->getItems() as $error) {
            try {
                $error->attempt($error->getId());
                $results['success']++;
            } catch (Exception $e) {
                $helper->writeError('An entry was place back in the queue: ' . $e->getMessage());
                $results['error']++;
            }
            $results['total']++;
        }

        return $results;
    }

    /**
     * Validates that certain fields are not empty
     *
     * @param array   $groups
     * @param boolean $formatWeb (Optional)
     *
     * @throws Mage_Exception
     */
    protected function _validateSupportForm($groups, $formatWeb = true)
    {
        $helper = Mage::helper(self::NOTICE_IDENTIFIER);

        $errors = array();
        foreach ($this->_validatedFields as $field => $label) {
            $values = $groups['support']['fields'][$field];
            if (array_key_exists('inherit', $values) && $values['inherit']) {
                continue;
            }

            if (empty($values['value'])) {
                $errors[] = $helper->__("Please enter your $label.");
            }
        }

        if (!empty($groups['support']['fields']['using_solution_partner']['value'])) {
            if (array_key_exists('inherit', $groups['support']['fields']['partner']) && $groups['support']['fields']['partner']['inherit']) {
                return;
            }

            if (empty($groups['support']['fields']['partner']['value'])) {
                $errors[] = $helper->__('Please enter your Solution Partner or SI Name.');
            }
        }

        if ($errors) {
            Mage::throwException(implode($formatWeb ? '<br/>' : "\n", $errors));
        }
    }

    /**
     * Save registration from from admin save config button
     * events: model_config_data_save_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return boolean
     */
    public function registerExtension(Varien_Event_Observer $observer)
    {
        $action  = $observer->getEvent()->getControllerAction();
        $session = Mage::getSingleton('admin/session');
        $support = Mage::helper(self::SUPPORT_IDENTIFIER);

        if (
            $session->isLoggedIn() &&
            !$action->getRequest()->isAjax() &&
            $action->getRequest()->isPost() &&
            $action->getRequest()->getParam('section') == 'bronto'
        ) {

            $groups  = $action->getRequest()->getParam('groups');
            $enabled = $groups['settings']['fields']['enabled']['value'];

            // If Module is not enabled, don't proceed
            if ($enabled == '0') {
                return false;
            }

            $apiToken = $groups['settings']['fields']['api_token']['value'];

            if (empty($apiToken)) {
                return false;
            }

            if (empty($groups['support'])) {
                return false;
            }

            try {
                $this->_validateSupportForm($groups);

                $postFields = array();
                foreach ($groups['support']['fields'] as $field => $values) {
                    if (array_key_exists('inherit', $groups['support']['fields'][$field]) && $groups['support']['fields'][$field]['inherit']) {
                        continue;
                    }
                    $postFields[$field] = $values['value'];
                }

                return $support->submitSupportForm($postFields);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addMessage(
                        Mage::getSingleton('core/message')
                            ->error($e->getMessage())
                            ->setIdentifier(self::NOTICE_IDENTIFIER)
                    );

                Mage::helper(self::NOTICE_IDENTIFIER)->writeError($e->getMessage());
            }
        }

        return false;
    }
}
