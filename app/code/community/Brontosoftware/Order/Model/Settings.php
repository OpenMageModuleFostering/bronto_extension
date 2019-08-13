<?php

class Brontosoftware_Order_Model_Settings extends Brontosoftware_Magento_Order_Settings
{
    /**
     * Override for auto injection
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getModel('brontosoftware_connector/impl_core_scoped'),
            Mage::getModel('brontosoftware_connector/settings'),
            Mage::getModel('brontosoftware_connector/impl_core_productCacheBridge'));
    }

    /**
     * @see parent
     */
    public function getTidHash($scope = 'default', $scopeId = null)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $installDate = Mage::getConfig()->getNode(Mage_Core_Model_App::XML_PATH_INSTALL_DATE);
        return md5($websiteId . $installDate);
    }
}
