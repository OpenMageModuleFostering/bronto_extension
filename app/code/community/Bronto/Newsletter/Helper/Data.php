<?php

/**
 * @package   Newsletter
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.3.5
 */
class Bronto_Newsletter_Helper_Data extends Bronto_Common_Helper_Data
{
    const XML_PATH_ENABLED = 'bronto_newsletter/settings/enabled';
    const XML_PATH_LIMIT = 'bronto_newsletter/settings/limit';
    const XML_PATH_SYNC_LIMIT = 'bronto_newsletter/settings/sync_limit';
    const XML_PATH_DEFAULT = 'bronto_newsletter/checkout/default_checked';
    const XML_PATH_SHOW_GUEST = 'bronto_newsletter/checkout/show_to_guests';
    const XML_PATH_SHOW_REGISTRAR = 'bronto_newsletter/checkout/show_to_registrars';
    const XML_PATH_SHOW_SUBSCRIBED = 'bronto_newsletter/checkout/show_if_subscribed';
    const XML_PATH_LABEL_TEXT = 'bronto_newsletter/checkout/label_text';
    const XML_PATH_USE_CUSTOM_TEMPLATE = 'bronto_newsletter/checkout/use_custom_template';
    const XML_PATH_BILLING_TEMPLATE = 'bronto_newsletter/checkout/billing_template';
    const XML_PATH_INSTALL_DATE = 'bronto_newsletter/settings/install_date';
    const XML_PATH_UPGRADE_DATE = 'bronto_newsletter/settings/upgrade_date';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_ENABLED);
    }

    /*
     * Get Text to display in notice when enabling module
     *
     * @return string
     */
    public function getModuleEnabledText()
    {
        $message = parent::getModuleEnabledText();
        $scopeData = $this->getScopeParams();
        if ($scopeData['scope'] != 'default') {
            $message = $this->__(
                'If the API token being used for this configuration scope is different from that of the Default Config scope, ' .
                'you should un-check the `Use Website` or `Use Default` for the <em>Add to List(s)</em> field in the <em>Contacts</em> ' .
                'group on this page and select the desired list(s).'
            );
        }
        return $message;
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
    public function getSyncLimit()
    {
        return (int)$this->getAdminScopedConfig(self::XML_PATH_SYNC_LIMIT);
    }

    /**
     * @return int
     */
    public function getLimit($storeId = null)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return (int)$this->getAdminScopedConfig(self::XML_PATH_LIMIT, $storeId);
    }

    /**
     * @return bool
     */
    public function isEnabledCheckedByDefault()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_DEFAULT);
    }

    /**
     * @return bool
     */
    public function isEnabledForGuestCheckout()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_SHOW_GUEST);
    }

    /**
     * @return bool
     */
    public function isEnabledForRegisterCheckout()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_SHOW_REGISTRAR);
    }

    /**
     * @return bool
     */
    public function isEnabledIfAlreadySubscribed()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_SHOW_SUBSCRIBED);
    }

    /**
     * @return string
     */
    public function getCheckboxLabelText()
    {
        return $this->getAdminScopedConfig(self::XML_PATH_LABEL_TEXT);
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return boolean
     */
    public function isCustomerSubscribed(Mage_Customer_Model_Customer $customer = null)
    {
        if (!$customer) {
            return false;
        }

        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
        return (bool)$subscriber->isSubscribed();
    }

    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        return 'Bronto_Newsletter';
    }

    /**
     * Get Count of Subscribers not in queue
     * @return int
     */
    public function getMissingSubscribersCount()
    {
        return Mage::getModel('bronto_newsletter/queue')
            ->getMissingSubscribersCount();
    }

    /**
     * Get Subscribers which aren't in queue
     * @return array
     */
    public function getMissingSubscribers()
    {
        return Mage::getModel('bronto_newsletter/queue')
            ->getMissingSubscribers();
    }
}
