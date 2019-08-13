<?php

class Brontosoftware_Migration_Helper_Data extends Mage_Core_Helper_Abstract
{
    private static $_modules = array(
        'all' => 'All Settings',
        'customer' => 'Contact Import',
        'newsletter' => 'Newsletter Opt-In',
        'order' => 'Order Import',
        'email' => 'Messages',
        'product' => 'Product Recommendations',
        'coupon' => 'Coupon Management',
        'popup' => 'Pop-up Manager Integration',
        'cartrecovery' => 'Cart Recovery Integration'
    );

    /**
     * Gets an array of selected modules to iterate over
     *
     * @param string $moduleName
     * @return array
     */
    public function getSelectedModules($moduleName = 'all')
    {
        if ($moduleName == 'all') {
            $moduleNames = (array)self::$_modules;
            array_shift($moduleNames);
            return $moduleNames;
        } else {
            return array($moduleName => self::$_modules[$moduleName]);
        }
    }

    /**
     * Gets all of the modules for Connector
     *
     * @return array
     */
    public function getBrontoModules()
    {
        $options = array();
        foreach (self::$_modules as $id => $name) {
            $options[] = array('id' => $id, 'name' => $this->__($name));
        }
        return $options;
    }

    /**
     * Gets the module helper to determine settings
     *
     * @param string $moduleName
     * @param string $scope
     * @param mixed $scopeId
     * @return boolean
     */
    public function isEnabled($moduleName, $scope = 'default', $scopeId = null)
    {
        $helper = null;
        switch ($moduleName) {
            case 'order':
            case 'newsletter':
            case 'customer':
            case 'email':
            case 'product':
                $helper = Mage::helper("bronto_{$moduleName}");
                break;
            default:
                $helper = Mage::helper('bronto_common');
        }
        if (!empty($helper)) {
            return $helper->isEnabled($scope, $scopeId);
        }
        return false;
    }

    /**
     * Gets a setting scanner from the desired module id
     *
     * @param string $moduleName
     * @return Brontosoftware_Migration_Model_Scanner
     */
    public function getScanner($moduleName)
    {
        return Mage::getModel("brontosoftware_migration/scanner_{$moduleName}");
    }
}
