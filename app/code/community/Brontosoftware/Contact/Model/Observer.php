<?php

class Brontosoftware_Contact_Model_Observer extends Brontosoftware_Magento_Contact_ExtensionAbstract
{
    /**
     * Overridden for DI
     */
    public function __construct()
    {
        $settings = Mage::getSingleton('brontosoftware_contact/settings');
        $event = new Brontosoftware_Magento_Contact_Event_Source($settings);
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_orderCacheBridge'),
            Mage::getSingleton('brontosoftware_connector/impl_core_customer'),
            MAge::getSingleton('brontosoftware_connector/impl_core_emulation'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_queue'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            $settings,
            Mage::getSingleton('brontosoftware_connector/impl_connector_platform'),
            $event);
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_contact')->__($message);
    }

    public function updateEmailRequest($observer)
    {
        $action = $observer->getControllerAction();
        $session = Mage::getSingleton('customer/session')->getCustomerId();
        $this->updateEmail($session, $action->getRequest()->getParam('email'));
    }

    /**
     * @see parent
     */
    protected function _contactCollection()
    {
        return Mage::getModel('customer/customer')->getCollection();
    }

    /**
     * @see parent
     */
    protected function _orderCollection()
    {
        return Mage::getModel('sales/order')->getCollection();
    }
}
