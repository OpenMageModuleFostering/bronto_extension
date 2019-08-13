<?php

class Brontosoftware_Coupon_Model_Observer extends Brontosoftware_Magento_Coupon_ExtensionAbstract
{
    protected $_formatHelper;

    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_rules'),
            Mage::getSingleton('brontosoftware_coupon/manager'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_middleware'),
            Mage::getSingleton('brontosoftware_connector/impl_core_emulation'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_queue'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            Mage::helper('brontosoftware_coupon'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_platform'),
            new Brontosoftware_Magento_Coupon_Event_Source());
        $this->_formatHelper = Mage::helper('salesRule/coupon');
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_coupon')->__($message);
    }

    /**
     * @see parent
     */
    protected function _couponFormats()
    {
        $formats = array();
        foreach ($this->_formatHelper->getFormatsList() as $id => $name) {
            $formats[] = array( 'id' => $id, 'name' => $name );
        }
        return $formats;
    }
}
