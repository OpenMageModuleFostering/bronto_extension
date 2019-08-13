<?php

/**
 * @package     Bronto\Customer
 * @copyright   2011-2012 Bronto Software, Inc.
 */
class Bronto_Email_Model_System_Config_Backend_Templates_Sendtype extends Mage_Core_Model_Config_Data
{
    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $realpath = str_replace('-', '/', array_pop(explode('/', $this->getPath())));
        $this->_saveConfigData($realpath, $this->getValue());

        parent::_beforeSave();
    }

    /**
     * @param type $path
     * @param type $value
     * @return Bronto_Email_Model_System_Config_Backend_Templates_Field
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
