<?php

/**
 * XML configuration base
 *
 * PHP version 5
 *
 * The license text...
 *
 * @category  Bronto
 * @package   ConflictChecker
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   CVS: $Id:$
 * @link      <>
 * @see       References to other sections (if any)...
 */

/**
 * XML configuration base
 *
 * @category  Bronto
 * @package   ConflictChecker
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   Release: @package_version@
 * @link      <>
 * @see       References to other sections (if any)...
 */
class Bronto_ConflictChecker_Model_Core_Config_Base
    extends Bronto_ConflictChecker_Model_Lib_Varien_Simplexml_Config
{
    //  {{{ __construct()

    /**
     * Constructor
     *
     * @return void
     * @access public
     */
    public function __construct($sourceData = null)
    {
        $this->_elementClass = 'Bronto_ConflictChecker_Model_Core_Config_Element';
        parent::__construct($sourceData);
    }

    //  }}}
}
