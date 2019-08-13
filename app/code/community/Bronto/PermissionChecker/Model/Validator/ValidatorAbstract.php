<?php

/**
 * Abstracted validator
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
 * Abstracted validator
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
abstract class Bronto_PermissionChecker_Model_Validator_ValidatorAbstract
    extends Mage_Core_Model_Abstract
    implements Bronto_PermissionChecker_Model_Validator_ValidatorInterface
{
    //  {{{ properties


    /**
     * Link List
     *
     * This is the pointer to the next node in the link list
     * @var object
     * @access protected
     */
    protected $_nextHandler = NULL;

    //  }}}
    //  {{{ _construct()


    /**
     * pseudo constructor
     *
     * @return void
     * @access public
     */
    public function _construct()
    {
        if (isset($this->_data[0])) {
            $this->_nextHandler = $this->_data[0];
        }
    }

    //  }}}
    //  {{{ validateSetting()


    /**
     * Validate the settings
     *
     * If there are no more links in the list, then return the growing
     * array of bad files to report on.  Else call to the next validatore to
     * check the node
     *
     * @param SplFileInfo $file     File to validate
     * @param array       $badFiles existing bad files to report on
     *
     * @return array
     * @access public
     */
    public function validateSetting(SplFileInfo $file, array $badFiles)
    {
        if (!is_null($this->_nextHandler)) {
            return $this->_nextHandler->validateSetting($file, $badFiles);
        } else {
            return $badFiles;
        }
    }

    //  }}}
}
