<?php

class Brontosoftware_Optin_Model_Observer extends Brontosoftware_Magento_Optin_ExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        $settings = Mage::getModel('brontosoftware_optin/settings');
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_subscriber'),
            Mage::getSingleton('brontosoftware_connector/impl_core_emulation'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_queue'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            $settings,
            Mage::getSingleton('brontosoftware_connector/impl_connector_platform'),
            new Brontosoftware_Magento_Optin_Event_Source($settings));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_optin')->__($message);
    }

    /**
     * @see parent
     */
    public function subscribeAfterCheckout($observer)
    {
        $checkout = Mage::getSingleton('checkout/session');
        $order = $observer->getOrder();
        $this->_subscribeAfterCheckout(
            $order->getStoreId(),
            $order->getCustomerEmail(),
            $checkout->hasSubscribeToNewsletter());
    }
}
