<?php

/**
 * Render the block view
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
 * Render the block view
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
class Bronto_PermissionChecker_Model_Validator_Printer
{
    //  {{{ render()


    /**
     * Render block view
     *
     * @param array $errors bad files to print
     *
     * @return string
     * @access public
     */
    public function render(array $errors)
    {
        $block = Mage::app()->getLayout()->createBlock('bronto_permissionchecker/printer');
        $block->setErrors($errors);
        return $block->toHtml();
    }

    //  }}}
}
