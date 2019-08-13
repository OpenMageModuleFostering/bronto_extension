<?php

class Brontosoftware_Optin_Block_Checkout extends Mage_Core_Block_Template
{
    protected $_layoutType = 'custom';
    protected $_checkout;
    protected $_dynamic = false;

    /**
     * @see parent
     */
    protected function _construct()
    {
        $this->_checkout = Mage::getSingleton('brontosoftware_optin/checkout');
    }

    /**
     * Sets the layout type for this block
     *
     * @param string $layoutType
     * @return $this
     */
    public function setCheckoutType($layoutType)
    {
        $this->_layoutType = $layoutType;
        return $this;
    }

    /**
     * Sets the dynamic flag for the checkbox for lazy loading
     *
     * @param boolean $dynamic
     * @return $this
     */
    public function setDynamic($dynamic)
    {
        $this->_dynamic = $dynamic;
        return $this;
    }

    /**
     * Determines if the checkout form is visible
     *
     * @return boolean
     */
    public function isCheckboxVisible()
    {
        $storeId = Mage::app()->getStore()->getId();
        $customer = null;
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_checkout->isCheckboxVisible($storeId, $customer, $this->_layoutType);
    }

    /**
     * Determines if the checkout box is ticked
     *
     * @return boolean
     */
    public function isCheckedByDefault()
    {
        return $this->_checkout->isCheckboxChecked(Mage::app()->getStore());
    }

    /**
     * @return boolean
     */
    public function isDynamic()
    {
        return $this->_dynamic;
    }

    /**
     * Gets the layout type of the form
     *
     * @return string
     */
    public function getCheckoutType()
    {
        return $this->_layoutType;
    }

    /**
     * Gets the checkbox label
     *
     * @return string
     */
    public function getCheckboxLabel()
    {
        $helper = Mage::getSingleton('brontosoftware_optin/settings');
        $label = $helper->getCheckoutLabel('store', Mage::app()->getStore());
        return $label;
    }

    /**
     * Gets the frontend subscriber url
     *
     * @return string
     */
    public function getSubscriberUrl()
    {
        $store = Mage::app()->getStore();
        return $store->getUrl('brontosoftware/optin/checkout', array('_secure' => $store->isCurrentlySecure()));
    }
}
