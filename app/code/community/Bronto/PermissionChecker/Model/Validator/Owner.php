<?php

/**
 * File Owner Validator
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
 * File Owner Validator
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
class Bronto_PermissionChecker_Model_Validator_Owner
    extends Bronto_PermissionChecker_Model_Validator_ValidatorAbstract
{
    //  {{{ validateSetting()


    /**
     * Validate Owner
     *
     * Checks to see if file owner setting matches expected
     *
     * @param SplFileInfo $file     File to check
     * @param array       $badFiles current array of bad files to report
     *
     * @return array
     * @access public
     */
    public function validateSetting(SplFileInfo $file, array $badFiles)
    {
        $targetOwner = Mage::getStoreConfig('permission_checker/settings/owner');
        if (!empty($targetOwner)) {
            //  Account for name and/or gid
            if (filter_var($targetGroup, FILTER_VALIDATE_INT)) {
                $actualOwner = $file->getOwner();
            } else {
                $owner = posix_getpwuid($file->getOwner());
                $actualOwner = $owner['name'];
            }
            if ($actualOwner != $targetOwner) {
                $path = substr_replace($file->__toString(), '', 0, strlen(Mage::getBaseDir()) + 1);
                $badFiles[$path]['owner'] = $actualOwner;
            }
        }
        return parent::validateSetting($file, $badFiles);
    }

    //  }}}
}
