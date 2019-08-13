<?php

class Brontosoftware_Optin_Block_Webform extends Mage_Customer_Block_Newsletter
{
    private $_helper;

    /**
     * @see parent
     */
    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::getModel('brontosoftware_optin/settings');
        if ($this->_helper->isFormEnabled('store', Mage::app()->getStore())) {
            $this->setTemplate('brontosoftware/optin/webform.phtml');
        } else {
            $this->setTemplate("customer/form/newsletter.phtml");
        }
    }

    /**
     * Forwards the call to the helper
     *
     * @return int
     */
    public function getWebformHeight()
    {
        return $this->_helper->getWebformHeight('store', Mage::app()->getStore());
    }

    /**
     * Forwadrs the call to the helper
     *
     * @return string
     */
    public function getWebformUrl()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return $this->_helper->getWebformUrl($customer->getEmail(), 'store', Mage::app()->getStore());
    }
}
