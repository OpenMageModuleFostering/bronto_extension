<?php

class Brontosoftware_Migration_Model_Scanner_Coupon extends Brontosoftware_Migration_Model_Scanner
{
    const MODULE_PATH = 'bronto_coupon/%';

    protected $_fieldsToLabel = array(
        'enabled' => 'Enabled',
        'coupon_code_param' => 'Coupon Code Query Parameter',
        'error_message_param' => 'Invalid Coupon Query Parameter',
        'success_mesasge' => 'Success Message',
        'invalid' => 'Invalid Message',
        'depleted' => 'Depleted Message',
        'expired' => 'Expired Message',
        'conflict' => 'Conflict Message',
        'link' => 'Link Text'
    );

    /**
     * @see parent
     */
    protected function _modulePath()
    {
        return self::MODULE_PATH;
    }
}
