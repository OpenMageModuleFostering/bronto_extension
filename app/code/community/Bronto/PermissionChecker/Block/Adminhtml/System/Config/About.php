<?php

/**
 * About header for admin module config
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
 * About header for admin module config
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
class Bronto_PermissionChecker_Block_Adminhtml_System_Config_About
    extends Bronto_Common_Block_Adminhtml_System_Config_About
{
    //  {{{ properties


    /**
     * Module name
     * @var string
     * @access protected
     */
    protected $_module = 'bronto_permissionchecker';

    /**
     * User descriptive module name
     * @var string
     * @access protected
     */
    protected $_name   = 'Bronto File System Permissions Checker Module';

    //  }}}
}
