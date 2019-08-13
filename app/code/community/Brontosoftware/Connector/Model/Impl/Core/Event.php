<?php

class Brontosoftware_Connector_Model_Impl_Core_Event implements Brontosoftware_Magento_Core_Event_ManagerInterface
{
    /**
     * @see parent
     */
    public function dispatch($eventName, array $data = array())
    {
        Mage::dispatchEvent($eventName, $data);
    }
}
