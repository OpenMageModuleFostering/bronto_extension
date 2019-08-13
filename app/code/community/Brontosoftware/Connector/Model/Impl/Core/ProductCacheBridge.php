<?php

class Brontosoftware_Connector_Model_Impl_Core_ProductCacheBridge extends Brontosoftware_Magento_Core_Catalog_ProductCacheAbstract
{
    protected $_productCache = array();

    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_imageHelperBridge'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            new Brontosoftware_Magento_Core_Catalog_ProductCategoryResolverImpl(
                Mage::getSingleton('brontosoftware_connector/impl_core_categoryCacheBridge'),
                Mage::getSingleton('brontosoftware_connector/impl_core_categoryResolverBridge')));
    }

    /**
     * @see parent
     */
    public function getById($productId, $storeId = null)
    {
        $cacheKey = "{$productId}:{$storeId}";
        if (!array_key_exists($cacheKey, $this->_productCache)) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($storeId)
                ->load($productId);
            if ($product->getId()) {
                $this->_productCache[$cacheKey] = $product;
            } else {
                $this->_productCache[$cacheKey] = null;
            }
        }
        return $this->_productCache[$cacheKey];
    }

    /**
     * @see parent
     */
    public function getBySku($productSku, $storeId = null)
    {
        $cacheKey = "{$productSku}-sku:{$storeId}";
        if (!array_key_exists($cacheKey, $this->_productCache)) {
            $products = Mage::getModel('catalog/product')->getCollection()
                ->addFieldToFilter('sku', array('eq' => $productSku));
            $this->_productCache[$cacheKey] = null;
            foreach ($products as $product) {
                $product->setStoreId($storeId);
                $product->setStockItem(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product));
                $this->_productCache[$cacheKey] = $product;
            }
        }
        return $this->_productCache[$cacheKey];
    }

    /**
     * @see parent
     */
    public function getBySource(Brontosoftware_Magento_Connector_Discovery_Source $source)
    {
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('visibility');
        $filters = $source->getFilters();
        if (array_key_exists('sku', $filters)) {
            $products->addFieldToFilter('sku', array( 'like' => "%{$filters['sku']}%" ));
        }
        if (array_key_exists('name', $filters)) {
            $products->addAttributeToFilter('name', array( 'like' => "%{$filters['name']}%" ));
        }
        if ($source->getId()) {
            $products->addFieldToFilter('entity_id', array( 'eq' => $source->getId() ) );
        }
        $products->getSelect()->limitPage($source->getOffset(), $source->getLimit());
        return $products;
    }

    /**
     * @see parent
     */
    public function getChildrenIds($productId, $storeId = null)
    {
        $product = $productId;
        if (is_numeric($productId)) {
            $product = $this->getById($productId, $storeId);
        }
        if (is_null($product) || $product->getTypeId() == 'simple') {
            return array();
        }
        $childrenIds = $product->getTypeInstance(true)->getChildrenIds($product->getId(), true);
        if (!empty($childrenIds)) {
            return $childrenIds[0];
        }
        return $childrenIds;
    }

    /**
     * @see parent
     */
    public function getParent($productId, $storeId = null)
    {
        $product = $productId;
        if (is_numeric($productId)) {
            $product = $this->getById($productId, $storeId);
        }
        if (is_null($product) || $product->isComposite()) {
            return null;
        }
        $factory = Mage::getSingleton('catalog/product_type');
        $composites = $factory->getCompositeTypes();
        $allTypes = $factory->getTypes();
        foreach ($composites as $typeId) {
            if (!empty($allTypes[$typeId]['model'])) {
                $instance = $allTypes[$typeId]['model'];
                $typeModel = Mage::getModel($instance);
                $typeModel->setConfig($allTypes[$typeId]);
                $parents = $typeModel->getParentIdsByChild($product->getId());
                if (!empty($parents)) {
                    return $this->getById($parents[0], $product->getStoreId());
                }
            }
        }
        return null;
    }
}
