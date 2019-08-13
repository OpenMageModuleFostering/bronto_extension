<?php

/**
 * Rewrite checker interface
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
 * Rewrite checker interface
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
interface Bronto_ConflictChecker_Model_Config_ConfigInterface
{
    //  {{{ getRewrites()


    /**
     * find all rewrites on XML node elements
     *
     * @param Bronto_ConflictChecker_Model_Core_Config_Element $config XML node
     * @access public
     */
    public function getRewrites(Bronto_ConflictChecker_Model_Core_Config_Element $config);

    //  }}}
}
