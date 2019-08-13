<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
abstract class Bronto_Common_Model_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{
    /**
     * @var string
     */
    protected $_cron_string_path;

    /**
     * @var string
     */
    protected $_cron_model_path;

    /**
     * @var string
     */
    protected $_xml_path_enabled = 'enabled';

    /**
     * Cron settings after save
     *
     * @return Bronto_Common_Model_System_Config_Backend_Cron
     */
    protected function _afterSave()
    {
        $cronExprString = '';

        if ($this->getFieldsetDataValue($this->_xml_path_enabled)) {
            $minutely  = Bronto_Common_Model_System_Config_Source_Cron_Frequency::CRON_MINUTELY;
            $hourly    = Bronto_Common_Model_System_Config_Source_Cron_Frequency::CRON_HOURLY;
            $daily     = Bronto_Common_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
            $frequency = $this->getFieldsetDataValue('frequency');

            if ($frequency == $minutely) {
                $interval = (int) $this->getFieldsetDataValue('interval');
                $cronExprString = "*/{$interval} * * * *";
            } elseif ($frequency == $hourly) {
                $minutes = (int) $this->getFieldsetDataValue('minutes');
                if ($minutes >= 0 && $minutes <= 59) {
                    $cronExprString = "{$minutes} * * * *";
                } else {
                    Mage::throwException(Mage::helper('bronto_common')->__('Please, specify correct minutes of hour.'));
                }
            } elseif ($frequency == $daily) {
                $time = $this->getFieldsetDataValue('time');
                $timeMinutes = intval($time[1]);
                $timeHours = intval($time[0]);
                $cronExprString = "{$timeMinutes} {$timeHours} * * *";
            }
        }

        try {
            if (!empty($this->_cron_string_path)) {
                $this->_saveConfigData($this->_cron_string_path, $cronExprString);
            }
            if (!empty($this->_cron_model_path)) {
                $this->_saveConfigData($this->_cron_model_path, (string) Mage::getConfig()->getNode($this->_cron_model_path));
            }
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('adminhtml')->__('Unable to save Cron expression'));
        }
    }

    /**
     * Get value by key for new user data from <section>/groups/<group>/fields/<field>
     *
     * @return string
     */
    public function getFieldsetDataValue($key)
    {
        if (method_exists('Mage_Core_Model_Config_Data', 'getFieldsetDataValue')) {
            return parent::getFieldsetDataValue($key);
        }

        // Handle older Magento versions
        $data = $this->_getData('fieldset_data');
        if (is_array($data) && isset($data[$key])) {
            return $data[$key];
        }

        $data    = $this->getData();
        $groups  = isset($data['groups'])  ? $data['groups']  : array();
        $groupId = isset($data['group_id']) ? $data['group_id'] : array();
        foreach ($groups as $group => $fields) {
            $fields = isset($fields['fields']) ? $fields['fields'] : $fields;
            if ($group == $groupId) {
                if (isset($fields[$key]['value'])) {
                    return $fields[$key]['value'];
                }
            }
        }

        return null;
    }

    /**
     * @param type                                           $path
     * @param type                                           $value
     * @return Bronto_Common_Model_System_Config_Backend_Cron
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
