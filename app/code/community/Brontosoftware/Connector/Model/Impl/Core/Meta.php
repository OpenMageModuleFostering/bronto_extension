<?php

class Brontosoftware_Connector_Model_Impl_Core_Meta implements Brontosoftware_Magento_Core_MetaInterface
{
    /**
      * @see parent
     */
    public function getName()
    {
        return 'Magento';
    }

    /**
     * @see parent
     */
    public function getVersion()
    {
        return Mage::getVersion();
    }

    /**
     * @see parent
     */
    public function getEdition()
    {
        return Mage::getEdition();
    }

    /**
     * @see parent
     */
    public function getExtensionVersion()
    {
        return (string) Mage::getConfig()
            ->getNode('modules/Brontosoftware_Connector')
            ->version;
    }

    /**
     * @see parent
     */
    public function getAdminFrontName()
    {
        return (string) Mage::getConfig()
            ->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_ADMINHTML_ROUTER_FRONTNAME);
    }
}
