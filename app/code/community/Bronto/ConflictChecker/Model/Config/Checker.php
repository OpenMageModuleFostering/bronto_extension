<?php

/**
 * Conflict checker
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
 * Conflict checker
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
class Bronto_ConflictChecker_Model_Config_Checker
    extends Bronto_ConflictChecker_Model_Config_ConfigAbstract
{
    //  {{{ getConflicts()


    /**
     * Get the conflicts
     *
     * @param Bronto_ConflictChecker_Model_Core_Config_Element $config Parameter description (if any) ...
     *
     * @return unknown Return description (if any) ...
     * @access public
     */
    public function getConflicts(
        Bronto_ConflictChecker_Model_Core_Config_Element $config
    ) {
        $rewrites = $this->getRewrites($config);
        foreach ($rewrites as $type => $modules) {
            foreach ($modules as $module => $classes) {
                foreach ($classes as $class => $conflicts) {
                    if (count($classes[$class]) > 1) {
                        echo "$type : $module : $class is rewrite multiple times by";
                        var_dump($conflicts);
                    }
                }
            }
        }
        return $this->getRewrites($config);
    }

    //  }}}
}
