<?php

/**
 * @package     Bronto\Order
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.1.5
 */
class Bronto_Order_Model_System_Config_Source_Limit
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            50   => 50,
            100  => 100,
            250  => 250,
            500  => 500,
            1000 => 1000,
        );
    }
}
