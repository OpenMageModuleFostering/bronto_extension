<?php

/**
 * @package   Bronto\Customer
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.0.0
 */
class Bronto_Customer_Block_Adminhtml_System_Config_Form_Fieldset_Attributes_Address extends Bronto_Customer_Block_Adminhtml_System_Config_Form_Fieldset_Attributes
{
    /**
     * @var array<Mage_Customer_Model_Attribute>
     */
    private $_addressAttributes;

    /**
     * @var array<string>
     */
    protected $_ignoreAttributes = array(
        'firstname',
        'lastname',
        'middlename',
        'prefix',
        'region_id',
        'suffix',
    );

    /**
     * @return array
     */
    protected function _getAttributes()
    {
        return $this->_getAddressAttributes();
    }

    /**
     * @return array
     */
    private function _getAddressAttributes()
    {
        if ($this->_addressAttributes === null) {
            $this->_addressAttributes = Mage::getModel('customer/entity_address_attribute_collection')->addVisibleFilter();
        }

        return $this->_addressAttributes;
    }
}
