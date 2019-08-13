<?php

class Brontosoftware_Migration_Model_Scanner_Newsletter extends Brontosoftware_Migration_Model_Scanner
{
    const MODULE_PATH = 'bronto_newsletter/%';

    protected $_fieldsToLabel = array(
        'enabled' => 'Enabled',
        'label_text' => 'Checkbox Label',
        'default_checked' => 'Checked by Default',
        'lists' => 'Add Opt-Ins to Lists',
        'update_unsub' => 'Remove Opt-Outs from Lists'
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
    protected function _translateValue($section, $name, $value)
    {
        $value = parent::_translateValue($section, $name, $value);
        if ($name == 'lists') {
            $value = explode(',', $value);
        }
        return $value;
    }
}
