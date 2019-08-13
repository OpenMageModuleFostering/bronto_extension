<?php

/**
 * @package   Newsletter
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.3.5
 */
class Bronto_Newsletter_Helper_Data extends Bronto_Common_Helper_Data
{
    const XML_PATH_ENABLED             = 'bronto_newsletter/settings/enabled';
    const XML_PATH_LIMIT               = 'bronto_newsletter/settings/limit';
    const XML_PATH_DEFAULT             = 'bronto_newsletter/checkout/default_checked';
    const XML_PATH_SHOW_GUEST          = 'bronto_newsletter/checkout/show_to_guests';
    const XML_PATH_SHOW_REGISTRAR      = 'bronto_newsletter/checkout/show_to_registrars';
    const XML_PATH_SHOW_SUBSCRIBED     = 'bronto_newsletter/checkout/show_if_subscribed';
    const XML_PATH_LABEL_TEXT          = 'bronto_newsletter/checkout/label_text';
    const XML_PATH_USE_CUSTOM_TEMPLATE = 'bronto_newsletter/checkout/use_custom_template';
    const XML_PATH_BILLING_TEMPLATE    = 'bronto_newsletter/checkout/billing_template';
    const XML_PATH_INSTALL_DATE        = 'bronto_newsletter/settings/install_date';
    const XML_PATH_UPGRADE_DATE        = 'bronto_newsletter/settings/upgrade_date';

    /**
     * @param string $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        if (!$this->getApiToken($store)) {
            return false;
        }

        return (bool) $this->getAdminScopedConfig(self::XML_PATH_ENABLED, $store);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function disableModule()
    {
        return $this->_disableModule(self::XML_PATH_ENABLED);
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
     * @return bool
     */
    public function isEnabledCheckedByDefault()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_DEFAULT);
    }

    /**
     * @return bool
     */
    public function isEnabledForGuestCheckout()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_SHOW_GUEST);
    }

    /**
     * @return bool
     */
    public function isEnabledForRegisterCheckout()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_SHOW_REGISTRAR);
    }

    /**
     * @return bool
     */
    public function isEnabledIfAlreadySubscribed()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_SHOW_SUBSCRIBED);
    }

    /**
     * @return string
     */
    public function getCheckboxLabelText()
    {
        return Mage::getStoreConfig(self::XML_PATH_LABEL_TEXT);
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
        return (bool) $subscriber->isSubscribed();
    }

    /**
     * @return bool
     */
    public function useCustomBillingTemplate()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_USE_CUSTOM_TEMPLATE);
    }

    /**
     * @return string
     */
    public function getCustomBillingTemplate()
    {
        $template = Mage::getStoreConfig(self::XML_PATH_BILLING_TEMPLATE);

        if (!$this->useCustomBillingTemplate() || empty($template)) {
            return false;
        }

        return $template;
    }

    /**
     * @return string
     */
    public function getCheckoutOnepageBillingTemplate()
    {
        $customTemplate = $this->getCustomBillingTemplate();

        if (empty($customTemplate)) {
            if ($this->isEnabled()) {
                return 'bronto/newsletter/billing.phtml';
            } else {
                return 'checkout/onepage/billing.phtml';
            }
        }

        return $customTemplate;
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
}
