<?php

/**
 * @package   Newsletter
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.3.5
 */
class Bronto_Newsletter_Block_Checkout_Onepage_Newsletter extends Mage_Checkout_Block_Onepage_Abstract
{
    /**
     * @return bool
     */
    public function isSubscribed()
    {
        return Mage::helper('bronto_newsletter')->isCustomerSubscribed($this->getCustomer());
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::helper('bronto_newsletter')->isEnabled();
    }

    /**
     * @return bool
     */
    public function isEnabledCheckedByDefault()
    {
        return Mage::helper('bronto_newsletter')->isEnabledCheckedByDefault();
    }

    /**
     * @return bool
     */
    public function isEnabledForGuestCheckout()
    {
        return Mage::helper('bronto_newsletter')->isEnabledForGuestCheckout();
    }

    /**
     * @return bool
     */
    public function isEnabledForRegisterCheckout()
    {
        return Mage::helper('bronto_newsletter')->isEnabledForRegisterCheckout();
    }

    /**
     * @return bool
     */
    public function isEnabledIfAlreadySubscribed()
    {
        return Mage::helper('bronto_newsletter')->isEnabledIfAlreadySubscribed();
    }

    /**
     * @return bool
     */
    public function getCheckboxLabelText()
    {
        return Mage::helper('bronto_newsletter')->getCheckboxLabelText();
    }
}
