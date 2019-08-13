<?php

/**
 * @package     Bronto\Customer
 * @copyright   2011-2012 Bronto Software, Inc.
 */
class Bronto_Customer_Model_System_Config_Backend_Newfield extends Mage_Core_Model_Config_Data
{
    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->isValueChanged()) {
            /* @var $fieldObject Bronto_Api_Field */
            $fieldObject = Mage::helper('bronto_common')->getApi()->getFieldObject();

            $field        = $fieldObject->createRow();
            $field->name  = $fieldObject->normalize($this->getValue());
            $field->label = $this->getValue();
            $field->type  = Bronto_Api_Field::TYPE_TEXT;

            try {
                $field->save();
                $fieldObject->addToCache($field->name, $field);

                $this->_saveConfigData(str_replace('new_', '', $this->getPath()), $field->name);
                $this->setValue(null);
            } catch (Exception $e) {
                Mage::throwException(Mage::helper('adminhtml')->__('Unable to save new field'));
            }
        }

        return parent::_beforeSave();
    }

    /**
     * @param type $path
     * @param type $value
     * @return Bronto_Customer_Model_System_Config_Backend_Newfield
     */
    protected function _saveConfigData($path, $value)
    {
        Mage::getModel('core/config_data')
            ->load($path, 'path')
            ->setValue($value)
            ->setPath($path)
            ->save();

        return $this;
    }
}
