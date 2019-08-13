<?php

class Brontosoftware_Connector_Model_Impl_Core_Stock implements Brontosoftware_Magento_Core_Stock_ManagerInterface
{
    protected $_caches = array();

    /**
     * @see parent
     */
    public function getByProductId($productId, $storeId = null)
    {
        $cacheKey = "{$productId}:{$storeId}";
        if (!array_key_exists($cacheKey, $this->_caches)) {
            $bridge = Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge');
            $product = $bridge->getById($productId, $storeId);
            $stock = null;
            if ($product) {
                $stock = $this->_loadStock($product);
            }
            $this->_caches[$cacheKey] = $stock;
        }
        return $this->_caches[$cacheKey];
    }

    /**
     * Loads a stock item from a full product
     *
     * @param mixed $product
     * @return mixed
     */
    protected function _loadStock($product)
    {
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        if ($stock->getId()) {
            return $stock;
        }
        return null;
    }
}
