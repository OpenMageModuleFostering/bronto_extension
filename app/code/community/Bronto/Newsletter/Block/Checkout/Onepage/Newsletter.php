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
    public function isEnabledForLoggedinCheckout()
    {
        // TODO: This can be replaced when fourth case is added
        return true;
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
        return addslashes(Mage::helper('bronto_newsletter')->getCheckboxLabelText());
    }

    /**
     *
     * @param string $method
     * @return string
     */
    public function getJsCheckedCode($method)
    {
        $js = "";
        $methodName = 'isEnabledFor' . ucfirst($method) . 'Checkout';

        // Default Values
        $action = 'hide';
        $checked = 'false';
        $value = 'null';

        // If function exists, use it, otherwise we hide and disable values
        if (method_exists($this, $methodName)) {
            if ($this->$methodName()) {
                $action = 'show';
                if ($this->isSubscribed() || $this->isEnabledCheckedByDefault()) {
                    $checked = 'true';
                    $value = '1';
                }
            }
        }

        // If user is subscribed and enabled if already subscribed is not allowed,
        // Hide it, but set the values to true
        if ($this->isSubscribed() && !$this->isEnabledIfAlreadySubscribed()) {
            $action = 'hide';
            $checked = 'true';
            $value = '1';
        }

        // Create JS
        $js .= "Element.{$action}('register-customer-newsletter');\r\n";
        $js .= "$('billing:is_subscribed_box').checked    = {$checked};\r\n";
        $js .= "$('billing:is_subscribed').value          = {$value};\r\n";
        $js .= "$('billing:is_subscribed').value          = {$value};\r\n";

        return $js;
    }
}
