<?php

class Brontosoftware_Connector_Model_Impl_Core_GroupCacheBridge implements Brontosoftware_Magento_Core_Customer_GroupCacheInterface
{
    protected $_cache = array();

    /**
     * @see parent
     */
    public function getById($groupId)
    {
        if (!array_key_exists($groupId, $this->_cache)) {
            $group = Mage::getModel('customer/group')->load($groupId);
            if ($group->getId()) {
                $this->_cache[$groupId] = $group;
            } else {
                $this->_cache[$groupId] = null;
            }
        }
        return $this->_cache[$groupId];
    }
}
