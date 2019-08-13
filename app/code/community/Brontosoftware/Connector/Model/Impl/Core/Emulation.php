<?php

class Brontosoftware_Connector_Model_Impl_Core_Emulation implements Brontosoftware_Magento_Core_App_EmulationInterface
{
    private $_initialInfo;

    /**
     * @see parent
     */
    public function startEnvironmentEmulation($storeId, $area, $force)
    {
        $emulation = Mage::getModel('core/app_emulation');
        $this->_initialInfo = $emulation->startEnvironmentEmulation($storeId, $area, $force);
    }

    /**
     * @see parent
     */
    public function stopEnvironmentEmulation()
    {
        Mage::getModel('core/app_emulation')->stopEnvironmentEmulation($this->_initialInfo);
    }
}
