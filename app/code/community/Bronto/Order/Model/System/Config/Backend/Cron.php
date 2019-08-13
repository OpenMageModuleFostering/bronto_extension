<?php

/**
 * @package     Bronto\Order
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.1.5
 */
class Bronto_Order_Model_System_Config_Backend_Cron extends Bronto_Common_Model_System_Config_Backend_Cron
{
    /**
     * @var string
     */
    protected $_cron_string_path = 'crontab/jobs/bronto_order_import/schedule/cron_expr';

    /**
     * @var string
     */
    protected $_cron_model_path  = 'crontab/jobs/bronto_order_import/run/model';
}
