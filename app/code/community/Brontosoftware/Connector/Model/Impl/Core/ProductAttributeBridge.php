<?php

class Brontosoftware_Connector_Model_Impl_Core_ProductAttributeBridge implements Brontosoftware_Magento_Core_Catalog_ProductAttributeCacheInterface
{
    protected $_attributes;

    /**
     * @see parent
     */
    public function getOptionArray()
    {
        if (is_null($this->_attributes)) {
          $options = array();
          $this->_attributes = array();
          foreach ($this->getCollection() as $attribute) {
              $this->_attributes[] = array(
                  'id' => $attribute->getAttributeCode(),
                  'name' => $attribute->getFrontendLabel()
              );
          }
        }
        return $this->_attributes;
    }

    /**
     * @see parent
     */
    public function getCollection()
    {
        return Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('frontend_label', array('notnull' => true));
    }
}
