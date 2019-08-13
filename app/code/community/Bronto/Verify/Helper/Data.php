<?php

/**
 * Verify Helper
 *
 * @category  Bronto
 * @package   Bronto_Verify
 * @author    Adam Daniels <adam.daniels@atlanticbt.com>
 * @copyright 2013 Adam Daniels
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   0.1.0
 */
class Bronto_Verify_Helper_Data
    extends Bronto_Common_Helper_Data
{
    /**
     * Description for const
     */
    const XML_PATH_ROUNDTRIP_ROOT = 'bronto_verify/settings/';

    /**
     * Get the full path from path ending
     *
     * @param string $pathend The setting to get the path for
     *
     * @return string
     * @access public
     */
    public function getPath($pathend)
    {
        return self::XML_PATH_ROUNDTRIP_ROOT . $pathend;
    }

    /**
     * Set the value of a setting
     *
     * @param string $path  The setting path to set the value for
     * @param string $value
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function setStatus($path, $value, $scope = null, $scopeId = null)
    {
        $scope   = (in_array($scope, array('default', 'websites', 'stores'))) ? $scope : 'default';
        $scopeId = (is_int($scopeId)) ? $scopeId : 0;

        return Mage::getSingleton('core/config')
            ->saveConfig($path, $value, $scope, $scopeId);
    }
}
