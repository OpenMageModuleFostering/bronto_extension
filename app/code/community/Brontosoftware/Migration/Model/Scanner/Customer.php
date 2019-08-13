<?php

class Brontosoftware_Migration_Model_Scanner_Customer extends Brontosoftware_Migration_Model_Scanner
{
    const MODULE_PATH = 'bronto_customer/%';

    protected $_attributes;
    protected $_addresses;
    protected $_fieldsToLabel = array(
        'enabled' => 'Enabled'
    );

    /**
     * @see parent
     */
    protected function _modulePath()
    {
        return self::MODULE_PATH;
    }

    /**
     * @see parent
     */
    protected function _fieldToLabel($section)
    {
        if ($section == 'attributes') {
            return $this->_attributes();
        } else if (preg_match('/address/', $section)) {
            return $this->_addresses();
        }
        return parent::_fieldToLabel($section);
    }

    /**
     * @see parent
     */
    protected function _translateValue($section, $key, $value)
    {
        if ($value == '_none_') {
            return null;
        }
        return parent::_translateValue($section, $key, $value);
    }

    /**
     * getter for customer attrs
     *
     * @return array
     */
    protected function _attributes()
    {
        if (is_null($this->_attributes)) {
            $this->_attributes = $this->_fill(Mage::getModel('customer/entity_attribute_collection'));
        }
        return $this->_attributes;
    }

    /**
     * getter for address attrs
     *
     * @return array
     */
    protected function _addresses()
    {
        if (is_null($this->_addresses)) {
            $this->_addresses = $this->_fill(Mage::getModel('customer/entity_address_attribute_collection'));
        }
        return $this->_addresses;
    }

    /**
     * Iterate over collection to achieve result
     *
     * @param mixed $collection
     * @return array
     */
    protected function _fill($collection)
    {
        $attributes = array();
        foreach ($collection as $attribute) {
            if ($attribute->getFrontendLabel()) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }
        return $attributes;
    }
}
