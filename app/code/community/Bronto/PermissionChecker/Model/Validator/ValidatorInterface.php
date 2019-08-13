<?php

/**
 * Validator interface
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
 * Validator interface
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
interface Bronto_PermissionChecker_Model_Validator_ValidatorInterface
{
    //  {{{ validateSetting()


    /**
     * Validate business logic for chain of responsibility nodes
     *
     * @param SplFileInfo $file     File node to check
     * @param array       $badFiles existing bad files to report on
     *
     * @access public
     */
    public function validateSetting(SplFileInfo $file, array $badFiles);

    //  }}}
}
