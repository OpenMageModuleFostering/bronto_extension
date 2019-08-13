<?php

/**
 * Short description for file
 *
 * Long description (if any) ...
 *
 * PHP version 5
 *
 * The license text...
 *
 * @category  Bronto
 * @package   Newsletter
 * @author    Jeff Lambert <jeff.lambert@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   CVS: $Id:$
 * @link      <>
 * @see       References to other sections (if any)...
 */
/**
 * @author Jeff Lambert <jeff.lambert@atlanticbt.com>
 */
class Bronto_Newsletter_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
    public function _construct()
    {
        $this->_init('bronto_newsletter/queue', 'queue_id');
    }

    /**
     * Get Write adapter instance
     * @return type
     */
    public function getWriteAdapter()
    {
        return $this->_getWriteAdapter();
    }
}