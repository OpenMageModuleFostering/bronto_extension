<?php

/**
 * Validate file permission
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
 * Validate file permission
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
class Bronto_PermissionChecker_Model_Validator_File
    extends Bronto_PermissionChecker_Model_Validator_ValidatorAbstract
{
    //  {{{ validateSetting()


    /**
     * Validate file permissions
     *
     * Checks to see if file permissions match correctly
     *
     * @param SplFileInfo $file     File to check
     * @param array       $badFiles current array of bad files to report
     *
     * @return array
     * @access public
     */
    public function validateSetting(SplFileInfo $file, array $badFiles)
    {
        if ($file->isFile()) {
            $filePermission = Mage::getStoreConfig('permission_checker/settings/files');
            //  This allows us to handle how big a sub string to return
            //  which will dynamically account for extra permission bits. i.e. (sticky bits)
            $filePermLen = strlen($filePermission);
            $octalPerms = substr(sprintf('%o', $file->getPerms()), -$filePermLen);

            if ($octalPerms != $filePermission) {
                $path = substr_replace($file->__toString(), '', 0, strlen(Mage::getBaseDir()) + 1);
                $badFiles[$path]['perms'] = $octalPerms;
            }
        }
        return parent::validateSetting($file, $badFiles);
    }

    //  }}}
}
