<?php

/**
 * Locator Iterator
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
 * Locator Iterator
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
abstract class Bronto_ConflictChecker_Model_Path_Locator_IteratorAbstract
{
    //  {{{ properties

    /**
     * Locator implementation
     * @var Bronto_ConflictChecker_Model_Path_Locator_LocatorInterface
     * @access protected
     */
    protected $_iterator = null;

    //  }}}
    //  {{{ __construct()

    /**
     * Constructor
     *
     * @param Bronto_ConflictChecker_Model_Path_Locator_LocatorInterface $iterator
     *
     * @return void
     * @access public
     */
    public function __construct($iterator)
    {
        $this->_iterator = $iterator;
    }

    //  }}}
}
