<?php

class Brontosoftware_Connector_Model_Impl_Connector_Platform extends Brontosoftware_Magento_Connector_Event_Platform
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            new Brontosoftware_Transfer_Curl_Adapter(),
            new Brontosoftware_Serialize_Json_Standard(),
            Mage::getModel('brontosoftware_connector/settings'),
            Mage::getModel('brontosoftware_connector/impl_core_logger'),
            Mage::getModel('brontosoftware_connector/impl_core_meta'));
    }
}
