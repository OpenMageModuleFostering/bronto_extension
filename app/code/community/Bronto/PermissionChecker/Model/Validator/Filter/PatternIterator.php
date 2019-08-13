<?php

/**
 * File Filter iterator
 *
 * PHP version 5
 *
 * The license text...
 *
 * @category  Bronto
 * @package   PermissionChecker
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   CVS: $Id:$
 * @link      <>
 * @see       References to other sections (if any)...
 */

/**
 * File Filter iterator
 *
 * @category  Bronto
 * @package   PermissionChecker
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   Release: @package_version@
 * @link      <>
 * @see       References to other sections (if any)...
 */
class Bronto_PermissionChecker_Model_Validator_Filter_PatternIterator
    extends RecursiveFilterIterator
{
    //  {{{ accept()

    /**
     * Check file name to see if it matches anything that needs to be filtered
     *
     * @return boolean
     * @access public
     */
    public function accept()
    {
        $exclusions = Mage::getStoreConfig('permission_checker/settings/exclude');
        $exclusions = explode(',', $exclusions);
        $exclusions[] = '.';
        $exclusions[] = '..';
        array_walk($exclusions, create_function('&$val', '$val = trim($val);'));

        return !in_array($this->current()->getBasename(), $exclusions);
    }

    //  }}}
}
