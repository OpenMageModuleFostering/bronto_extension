<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Block_Adminhtml_System_Config_Cron extends Bronto_Common_Block_Adminhtml_System_Config_Cron
{
    /**
     * @var string
     */
    protected $_jobCode = 'bronto_reminder_send_notification';
}
