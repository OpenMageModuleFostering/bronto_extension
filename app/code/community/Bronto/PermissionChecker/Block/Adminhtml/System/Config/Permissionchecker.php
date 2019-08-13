<?php

/**
 * Permission checker
 *
 * This is the heart of the permission checker that glues together and fires
 * the Chain of responsibility
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
 * Permission checker
 *
 * This is the heart of the permission checker that glues together and fires
 * the Chain of responsibility
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
class Bronto_PermissionChecker_Block_Adminhtml_System_Config_Permissionchecker
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    //  {{{ render()


    /**
     * Render all files that don't validate to the proper permissions
     *
     * @param Varien_Data_Form_Element_Abstract $element Form element
     *
     * @return string
     * @access public
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        //  Chain of Responsibility
        //  each checker looks through its designated area to validate the node we're at.
        $file  = Mage::getModel('bronto_permissionchecker/validator_file');
        $dir   = Mage::getModel('bronto_permissionchecker/validator_directory', array($file));
        $group = Mage::getModel('bronto_permissionchecker/validator_group', array($dir));
        $owner = Mage::getModel('bronto_permissionchecker/validator_owner', array($group));

        $checker = Mage::getModel('bronto_permissionchecker/validator_checker', array($owner));

        $directory = new RecursiveDirectoryIterator(Mage::getBaseDir());
        $filter    = new Bronto_PermissionChecker_Model_Validator_Filter_PatternIterator($directory);
        $iterator  = new RecursiveIteratorIterator(
            $filter,
            RecursiveIteratorIterator::LEAVES_ONLY,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        $invalidFiles = $checker->validateSettings($iterator);

        $printer = new Bronto_PermissionChecker_Model_Validator_Printer();
        return $printer->render($invalidFiles);
    }

    //  }}}
}
