<?php

/**
 * Validate File Group
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
 * Validate File Group
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
class Bronto_PermissionChecker_Model_Validator_Group
    extends Bronto_PermissionChecker_Model_Validator_ValidatorAbstract
{
    //  {{{ validateSetting()


    /**
     * Validate Group
     *
     * Checks to see if file group setting matches expected
     *
     * @param SplFileInfo $file     File to check
     * @param array       $badFiles current array of bad files to report
     *
     * @return array
     * @access public
     */
    public function validateSetting(SplFileInfo $file, array $badFiles)
    {
        $targetGroup = Mage::getStoreConfig('permission_checker/settings/group');
        if (!empty($targetGroup)) {
            //  Account for name and/or gid
            if (filter_var($targetGroup, FILTER_VALIDATE_INT)) {
                $actualGroup = $file->getGroup();
            } else {
                $group = posix_getgrgid($file->getGroup());
                $actualGroup = $group['name'];
            }
            if ($actualGroup != $targetGroup) {
                $path = substr_replace($file->__toString(), '', 0, strlen(Mage::getBaseDir()) + 1);
                $badFiles[$path]['group'] = $actualGroup;
            }
        }
        return parent::validateSetting($file, $badFiles);
    }

    //  }}}
}
