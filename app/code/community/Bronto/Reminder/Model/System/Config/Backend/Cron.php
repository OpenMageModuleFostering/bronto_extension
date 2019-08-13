<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_System_Config_Backend_Cron extends Bronto_Common_Model_System_Config_Backend_Cron
{
    /**
     * @var string
     */
    protected $_cron_string_path = 'crontab/jobs/bronto_reminder_send_notification/schedule/cron_expr';

    /**
     * @var string
     */
    protected $_cron_model_path  = 'crontab/jobs/bronto_reminder_send_notification/run/model';
}
