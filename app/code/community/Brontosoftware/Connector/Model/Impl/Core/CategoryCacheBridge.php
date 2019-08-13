<?php

class Brontosoftware_Connector_Model_Impl_Core_CategoryCacheBridge implements Brontosoftware_Magento_Core_Catalog_CategoryCacheInterface
{
    protected $_categoryCache = array();

    /**
     * @see parent
     */
    public function getById($categoryId, $storeId = null)
    {
        if (!array_key_exists($categoryId, $this->_categoryCache)) {
            $category = Mage::getModel('catalog/category')
                ->setStoreId($storeId)
                ->load($categoryId);
            if ($category->getId()) {
                $this->_categoryCache[$categoryId] = $category;
            } else {
                return null;
            }
        }
        return $this->_categoryCache[$categoryId];
    }

    /**
     * @see parent
     */
    public function getBySource(Brontosoftware_Magento_Connector_Discovery_Source $source)
    {
        $categories = Mage::getModel('catalog/category')->getCollection()->addNameToResult();
        $filters = $source->getFilters();
        if (array_key_exists('name', $filters)) {
            $categories->addAttributeToFilter('name', array( 'like' => "%{$filters['name']}%" ));
        }
        if ($source->getId()) {
            $categories->addFieldToFilter('entity_id', array( 'eq' => $source->getId() ));
        }
        $categories->getSelect()->limitPage($source->getOffset(), $source->getLimit());
        return $categories;
    }
}
