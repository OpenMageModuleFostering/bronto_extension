<?php

/**
 * @package   Order
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.1.5
 */
class Bronto_Order_Model_Resource_Setup extends Mage_Sales_Model_Mysql4_Setup
{

    /**
     * Short description for function
     * 
     * Long description (if any) ...
     * 
     * @param string    $code Parameter description (if any) ...
     * @param unknown   $data Parameter description (if any) ...
     * @return string    Return description (if any) ...
     * @access protected
     */
    protected function _getAttributeColumnDefinition($code, $data)
    {
        $definition = parent::_getAttributeColumnDefinition($code, $data);

        if ($code === 'bronto_imported' && is_string($definition)) {
            return 'datetime NULL DEFAULT NULL';
        }

        return $definition;
    }
}
