<?php

/**
 * Locator factory
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
 * Locator factory
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
class Bronto_ConflictChecker_Model_Path_Locator_Factory
{
    //  {{{ getLocator()

    /**
     * Get path locator implementation based on PHP version
     *
     * @return Bronto_ConflictChecker_Model_Path_Locator_LocatorInterface
     * @access public
     */
    public function getLocator()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $model = new Bronto_ConflictChecker_Model_Path_Locator_Stack(new SplStack());
        } else {
            $model = new Bronto_ConflictChecker_Model_Path_Locator_Array(array());
        }

        return $model;
    }

    //  }}}
}
