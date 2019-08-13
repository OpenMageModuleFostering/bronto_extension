<?php

class Brontosoftware_Inventory_Model_Manager extends Brontosoftware_Connector_Model_Impl_Core_Stock implements Brontosoftware_Magento_Product_CatalogMapperManagerInterface
{
    /**
     * @see parent
     */
    public function getByProduct($product)
    {
        if (!array_key_exists($product->getId(), $this->_caches)) {
            $this->_caches[$product->getId()] = $this->_loadStock($product);
        }
        return $this->_caches[$product->getId()];
    }
}
