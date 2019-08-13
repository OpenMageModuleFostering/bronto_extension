<?php

class Brontosoftware_Migration_Model_Scanner_Popup extends Brontosoftware_Migration_Model_Scanner
{
    const MODULE_PATH = 'bronto_popup/%';

    protected $_fieldsToLabel = array(
        'code' => 'Domain',
        'subscribe' => 'Subscribe to Newsletter'
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
    protected function _translateValue($section, $key, $value) {
        $value = parent::_translateValue($section, $key, $value);
        if ($key == 'code') {
            if (preg_match('/bronto-popup-id="([^"]+)"/', $value, $matches)) {
                $value = array($matches[1]);
            }
        }
        return $value;
    }

    /**
     * @see parent
     */
    protected function _afterConfig($settings)
    {
        $settings = parent::_afterConfig($settings);
        if (array_key_exists('settings', $settings)) {
            $settings['settings']['enabled'] = array(
                'name' => 'Enabled',
                'value' => array_key_exists('code', $settings['settings'])
            );
        }
        return $settings;
    }
}
