<?php

class Brontosoftware_Product_Model_CategorySettings extends Brontosoftware_Magento_Product_CategorySettings
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(Mage::getSingleton('brontosoftware_connector/impl_core_scoped'));
    }
}
