<?php

/**
 * Table generator
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
 * Table generator
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
class Bronto_PermissionChecker_Block_Printer
    extends Mage_Adminhtml_Block_Template
{
    //  {{{ properties


    /**
     * Parity bit
     * @var integer
     * @access protected
     */
    protected $_i = 0;

    //  }}}
    //  {{{ _construct()


    /**
     * psuedo constructor
     *
     * @return void
     * @access public
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('bronto/permissionchecker/errors.phtml');
    }

    //  }}}
    //  {{{ getParity()


    /**
     * Get if even or odd
     *
     * @return string
     * @access public
     */
    public function getParity()
    {
        return $this->_i++ % 2 ? 'even' : '';
    }

    //  }}}
}
