<?php

/**
 * file system checker
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
 * File system checker
 *
 * This is the client of the Chain of Responsibility
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
class Bronto_PermissionChecker_Model_Validator_Checker
    extends Bronto_PermissionChecker_Model_Validator_ValidatorAbstract
{
    //  {{{ validateSettings()

    /**
     * Validate all settings defined in the chain of responsibility
     *
     * This is the client in the chain of responsibility
     *
     * @param RecursiveIteratorIterator $path Path to the beginning of the directory tree
     *
     * @return array                     All the files which were found that deviate from the expected settings
     * @access public
     */
    public function validateSettings(RecursiveIteratorIterator $path)
    {
        $badFiles = array();
        foreach ($path as $filePath => $fileInfo) {
            $badFiles = $this->validateSetting($fileInfo, $badFiles);
        }

        return $badFiles;
    }

    //  }}}
}
