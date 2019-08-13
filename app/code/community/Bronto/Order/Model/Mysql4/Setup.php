<?php

/**
 * @package   Order
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.1.7
 */
class Bronto_Order_Model_Mysql4_Setup extends Mage_Sales_Model_Mysql4_Setup
{

    /**
     * Get column definition for attribute
     * 
     * @param string    $code Parameter description (if any) ...
     * @param unknown   $data Parameter description (if any) ...
     * @return string   Return description (if any) ...
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
    
    /**
     * Remove entity attribute. Overwritten for flat entities support
     *
     * @param int|string $entityTypeId
     * @param string $code
     * @param array $attr
     * @return Mage_Sales_Model_Mysql4_Setup
     */
    public function removeAttribute($entityTypeId, $code)
    {
        if (isset($this->_flatEntityTables[$entityTypeId]) &&
            $this->_flatTableExist($this->_flatEntityTables[$entityTypeId]))
        {
            $this->_removeFlatAttribute($this->_flatEntityTables[$entityTypeId], $code);
            $this->_removeGridAttribute($this->_flatEntityTables[$entityTypeId], $code, $entityTypeId);
        } else {
            parent::removeAttribute($entityTypeId, $code);
        }
        return $this;
    }
     
    /**
     * Remove an attribute as separate column in the table
     * The sales setup class doesn't support it by default
     *
     * @param string $table
     * @param string $attribute
     * @param array $attr
     * @return Mage_Sales_Model_Mysql4_Setup
     */
    protected function _removeFlatAttribute($table, $attribute)
    {
        $this->getConnection()->dropColumn($this->getTable($table), $attribute);
        return $this;
    }
 
    /**
     * Remove attribute from grid
     *
     * @param string $table
     * @param string $attribute
     * @param array $attr
     * @param string $entityTypeId
     * @return Mage_Sales_Model_Mysql4_Setup
     */
    protected function _removeGridAttribute($table, $attribute, $entityTypeId)
    {
        if (in_array($entityTypeId, $this->_flatEntitiesGrid)) {
            $this->getConnection()->dropColumn($this->getTable($table . '_grid'), $attribute);
        }
        return $this;
    }
}
