<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.7
 */
class Bronto_Order_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;
    
    /**
     * Initialize Model
     * 
     * @return void  
     * @access public
     */
    public function _construct()
    {
        $this->_init('bronto_order/queue', 'queue_id');
    }
}