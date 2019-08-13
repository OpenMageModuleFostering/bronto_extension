<?php

/**
 * @category Bronto
 * @package Common
 */
class Bronto_Common_Model_Observer
{

    private $_validatedFields = array(
        'site_name' => 'Bronto Site Name',
        'firstname' => 'First Name',
        'lastname' => 'Last Name',
        'number' => 'Phone Number',
        'email' => 'Email',
        'title' => 'Job Title',
    );

    /**
     * Description for const
     */
    const NOTICE_IDENTIFER = 'bronto_common';

    const SUPPORT_IDENTIFIER = 'bronto_common/support';

    /**
     * events: controller_action_predispatch
     * @param Varien_Event_Observer $observer
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
            return;
        }

        $helper = Mage::helper(self::NOTICE_IDENTIFER);

        // Verify Requirements
        if (!$helper->varifyRequirements(self::NOTICE_IDENTIFER, array('soap', 'openssl'))) {
            return;
        }

        // Verify API tokens are valid
        if ($helper->isEnabled() && !$helper->validApiTokens(self::NOTICE_IDENTIFER)) {
            return false;
        }

        // Bug user about registration, only once
        if (!Mage::helper(self::SUPPORT_IDENTIFIER)->isRegistered()) {
            $appendix = '<a href="#bronto_support-head">below</a>.';
            if ($action->getRequest()->getParam('section') != 'bronto') {
                $registerUrl = Mage::getSingleton('adminhtml/url')
                    ->getUrl('*/system_config/edit', array('section' => 'bronto'));
                $appendix = '<a href="' . $registerUrl . '">here</a>.';
            }

            $message = Mage::getSingleton('core/message')
                ->warning($helper->__('Please register your Bronto extension ' . $appendix));
            $message->setIdentifier(self::NOTICE_IDENTIFER);
            $session = Mage::getSingleton('adminhtml/session');
            foreach ($session->getMessages()->getItemsByType('warning') as $setMessage) {
                if ($setMessage->getIdentifier() == $message->getIdentifier()) {
                    $exists = true;
                    break;
                }
            }

            if (empty($exists)) {
                $session->addMessage($message);
            }
        }
    }

    /**
     * Cron to clear downloaded zips
     */
    public function clearArchives($cron) {
        Mage::helper(self::SUPPORT_IDENTIFIER)->clearArchiveDirectory();
    }

    /**
     * Validates that certain fields are not empty
     *
     * @param array $config
     * @param boolean $formatWeb (Optional)
     * @throws Mage_Exception
     */
    protected function _validateSupportForm($groups, $formatWeb = true) {
        $helper = Mage::helper(self::NOTICE_IDENTIFER);

        $errors = array();
        foreach ($this->_validatedFields as $field => $label) {
            if ($groups['support']['fields'][$field]['inherit']) {
                continue;
            }

            if (empty($groups['support']['fields'][$field]['value'])) {
                $errors[] = $helper->__("Please enter your $label.");
            }
        }

        if (!empty($groups['support']['fields']['using_solution_partner']['value'])) {
            if ($groups['support']['fields']['partner']['inherit']) {
                continue;
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
     * @return boolean
     */
    public function registerExtension(Varien_Event_Observer $observer) {
        $action = $observer->getEvent()->getControllerAction();
        $session = Mage::getSingleton('admin/session');
        $support = Mage::helper(self::SUPPORT_IDENTIFIER);

        if (
            $session->isLoggedIn() &&
            !$action->getRequest()->isAjax() &&
            $action->getRequest()->isPost() &&
            $action->getRequest()->getParam('section') == 'bronto'
        ) {

            $groups = $action->getRequest()->getParam('groups');
            $apiToken = $groups['settings']['fields']['api_token']['value'];

            if (empty($apiToken)) {
                return false;
            }

            try {
                $this->_validateSupportForm($groups);

                $postFields = array();
                foreach ($groups['support']['fields'] as $field => $values) {
                    if ($groups['support']['fields'][$field]['inherit']) {
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
                        ->setIdentifier(self::NOTICE_IDENTIFER)
                    );

                Mage::helper(self::NOTICE_IDENTIFER)->writeError($e->getMessage());
            }
        }

        return false;
    }
}
