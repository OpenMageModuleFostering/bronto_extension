<?php

class Brontosoftware_Connector_Model_Impl_Connector_Middleware extends Brontosoftware_Magento_Connector_Middleware
{
    /**
     * Overridden constructor for DI purposes
     */
    public function __construct()
    {
        parent::__construct(
            new Brontosoftware_Transfer_Curl_Adapter(),
            new Brontosoftware_Serialize_Json_Standard(),
            Mage::getModel('brontosoftware_connector/impl_core_logger'),
            Mage::getModel('brontosoftware_connector/impl_core_meta'),
            Mage::getModel('brontosoftware_connector/impl_core_encryptor'),
            Mage::getModel('brontosoftware_connector/impl_core_event'),
            Mage::getModel('brontosoftware_connector/impl_core_store'),
            Mage::getModel('brontosoftware_connector/impl_core_config'),
            Mage::getSingleton('brontosoftware_connector/settings'));
    }
}
