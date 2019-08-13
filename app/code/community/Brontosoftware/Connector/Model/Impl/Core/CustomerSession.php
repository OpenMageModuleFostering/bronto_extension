<?php

class Brontosoftware_Connector_Model_Impl_Core_CustomerSession implements Brontosoftware_Magento_Core_Customer_SessionInterface
{
    /**
     * @see parent
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * @see parent
     */
    public function logout()
    {
        Mage::getSingleton('customer/session')->logout();
        return $this;
    }

    /**
     * @see parent
     */
    public function setBeforeAuthUrl($redirectUrl)
    {
        Mage::getSingleton('customer/session')->setBeforeAuthUrl($redirectUrl);
        return $this;
    }
}
