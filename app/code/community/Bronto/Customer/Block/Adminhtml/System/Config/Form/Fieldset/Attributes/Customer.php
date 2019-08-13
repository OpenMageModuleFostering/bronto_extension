<?php

/**
 * @package   Bronto\Customer
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.0.0
 */
class Bronto_Customer_Block_Adminhtml_System_Config_Form_Fieldset_Attributes_Customer extends Bronto_Customer_Block_Adminhtml_System_Config_Form_Fieldset_Attributes
{
    /**
     * @var array<Mage_Customer_Model_Attribute>
     */
    private $_customerAttributes;

    /**
     * @var array<string>
     */
    protected $_ignoreAttributes = array(
        'increment_id',
        'updated_at',
        'entity_id',
        'attribute_set_id',
        'entity_type_id',
        'password_hash',
        'default_billing',
        'default_shipping',
        'email',
        'confirmation',
        'reward_update_notification',
        'reward_warning_notification',
        'disable_auto_group_change',
    );
    
    protected $_configPath = Bronto_Customer_Helper_Data::XML_PREFIX_CUSTOMER_ATTR;
    protected $_fieldNameTemplate = 'groups[attributes][fields][_attrCode_][value]';

    /**
     * @return array
     */
    protected function _getAttributes()
    {
        return $this->_getCustomerAttributes();
    }

    /**
     * @return array
     */
    private function _getCustomerAttributes()
    {
        if ($this->_customerAttributes === null) {
            $this->_customerAttributes = Mage::getModel('customer/entity_attribute_collection');
        }

        return $this->_customerAttributes;
    }
}
