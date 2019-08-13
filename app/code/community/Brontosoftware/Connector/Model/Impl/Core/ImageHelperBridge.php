<?php

class Brontosoftware_Connector_Model_Impl_Core_ImageHelperBridge implements Brontosoftware_Magento_Core_Catalog_ImageHelperInterface
{
    protected $_logger;

    /**
     * Instantiates the logger
     */
    public function __construct()
    {
        $this->_logger = Mage::getModel('brontosoftware_connector/impl_core_logger');
    }

    /**
     * @see parent
     */
    public function getImageUrl($product, $attribute)
    {
        try {
            return (string) Mage::helper('catalog/image')->init($product, $attribute);
        } catch (Exception $e) {
            $this->_logger->critical($e);
            return '';
        }
    }

    /**
     * @see parent
     */
    public function getDefaultPlaceHolderUrl()
    {
        try {
            $placeholder = Mage::helper('catalog/image')->getPlaceholder();
            return Mage::getDesign()->getSkinUrl($placeholder);
        } catch (Exception $e) {
            $this->_logger->critical($e);
            return '';
        }
    }
}
