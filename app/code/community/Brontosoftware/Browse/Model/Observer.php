<?php

class Brontosoftware_Browse_Model_Observer extends Brontosoftware_Magento_Browse_ExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_queue'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            Mage::getSingleton('brontosoftware_browse/settings'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_platform'),
            new Brontosoftware_Magento_Browse_Event_Source(
                Mage::getSingleton('brontosoftware_browse/settings'),
                Mage::getSingleton('brontosoftware_connector/settings')));
    }

    /**
     * Handle the generic site fiddle
     *
     * @param mixed $observer
     * @return void
     */
    public function handleSiteFiddle($observer)
    {
        if ($this->_helper->isEnabled('store', $this->_storeManager->getStore())) {
            Mage::dispatchEvent('brontosoftware_browse_event', array(
                'request' => $observer->getRequest(),
                'url' => $observer->getRequest()->getParam('currentUrl'),
                'event_type' => 'VISIT'
            ));
        }
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_browse')->__($message);
    }
}
