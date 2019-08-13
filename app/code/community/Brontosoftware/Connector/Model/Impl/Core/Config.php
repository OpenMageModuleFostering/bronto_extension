<?php

class Brontosoftware_Connector_Model_Impl_Core_Config implements Brontosoftware_Magento_Core_Config_ManagerInterface, Brontosoftware_Magento_Core_Config_FactoryInterface
{
    /**
     * @see parent
     */
    public function save($path, $value, $scopeName, $scopeId)
    {
        Mage::getModel('core/config_data')
            ->setPath($path)
            ->setValue($value)
            ->setScope($this->_fixScope($scopeName))
            ->setScopeId($scopeId)
            ->save();
    }

    /**
     * @see parent
     */
    public function reinit()
    {
        Mage::getConfig()->reinit();
    }

    /**
     * @see parent
     */
    public function deleteAll($path, $scopeName, $scopeId)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();
        $tableName = Mage::getModel('core/config_data')->getResource()->getMainTable();
        $conditions = array(
            $connection->quoteInto('path LIKE ?', "{$path}%"),
            $connection->quoteInto('scope=?', $this->_fixScope($scopeName)),
            $connection->quoteInto('scope_id=?', $scopeId)
        );
        $connection->delete($tableName, $conditions);
        $connection->commit();
    }

    /**
     * @see parent
     */
    public function getCollection()
    {
        return Mage::getModel('core/config_data')->getCollection();
    }

    /**
     * Fixes the scope names
     *
     * @param string $scopeName
     * @return string
     */
    private function _fixScope($scopeName)
    {
        if ($scopeName == 'store' || $scopeName == 'website') {
            $scopeName .= 's';
        }
        return $scopeName;
    }
}
