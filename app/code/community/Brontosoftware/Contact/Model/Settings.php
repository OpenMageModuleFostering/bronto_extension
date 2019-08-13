<?php

class Brontosoftware_Contact_Model_Settings extends Brontosoftware_Magento_Contact_SettingsAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getModel('brontosoftware_connector/impl_core_store'),
            Mage::getModel('brontosoftware_connector/impl_core_groupCacheBridge'),
            Mage::getModel('brontosoftware_connector/impl_core_config'),
            Mage::getModel('brontosoftware_connector/impl_core_scoped'),
            Mage::getModel('brontosoftware_connector/impl_core_event'));
    }

    /**
     * @see parent
     */
    public function getAttributes()
    {
        $attributes = array();
        $attributes[] = Mage::getModel('customer/entity_attribute_collection');
        $attributes[] = Mage::getModel('customer/entity_address_attribute_collection');
        $attributes[] = Mage::getModel('customer/entity_address_attribute_collection');
        return array_combine(self::$_attributeKeys, $attributes);
    }

    /**
     * @see parent
     */
    public function getAttributeLabels()
    {
        $helper = Mage::helper('brontosoftware_contact');
        $attributes = array();
        $attributes[] = $helper->__('Attributes');
        $attributes[] = $helper->__('Billing Address Attributes');
        $attributes[] = $helper->__('Shipping Address Attributes');
        return array_combine(self::$_attributeKeys, $attributes);
    }
}
