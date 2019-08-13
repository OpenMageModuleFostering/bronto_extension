<?php

class Brontosoftware_Migration_Model_Scanner_Cartrecovery extends Brontosoftware_Migration_Model_Scanner
{
    const MODULE_PATH = 'bronto_cartrecovery/%';

    protected $_fieldsToLabel = array(
        'code' => 'Embed Code',
        'other' => 'Other Product Attribute'
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
