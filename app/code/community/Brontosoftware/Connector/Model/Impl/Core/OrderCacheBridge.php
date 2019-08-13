<?php

class Brontosoftware_Connector_Model_Impl_Core_OrderCacheBridge implements Brontosoftware_Magento_Core_Sales_OrderCacheInterface
{
    protected $_cache = array();

    /**
     * @see parent
     */
    public function getById($orderId)
    {
        if (!array_key_exists($orderId, $this->_cache)) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $this->_cache[$orderId] = $order;
            } else {
                $this->_cache[$orderId] = null;
            }
        }
        return $this->_cache[$orderId];
    }
}
