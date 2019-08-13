<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.5
 */
class Bronto_Order_Helper_Data extends Bronto_Common_Helper_Data implements Bronto_Common_Helper_DataInterface
{
    const XML_PATH_ENABLED      = 'bronto_order/settings/enabled';
    const XML_PATH_LIMIT        = 'bronto_order/settings/limit';
    const XML_PATH_DESCRIPTION  = 'bronto_order/settings/description_attribute';
    const XML_PATH_INSTALL_DATE = 'bronto_order/settings/install_date';
    const XML_PATH_UPGRADE_DATE = 'bronto_order/settings/upgrade_date';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function disableModule($scope = 'default', $scopeId = 0)
    {
        return $this->_disableModule(self::XML_PATH_ENABLED, $scope, $scopeId);
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return (int) Mage::getStoreConfig(self::XML_PATH_LIMIT);
    }

    /**
     * @return string
     */
    public function getDescriptionAttribute()
    {
        return Mage::getStoreConfig(self::XML_PATH_DESCRIPTION);
    }

    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        return 'Bronto_Order';
    }
    
    /**
     * Get Item Product Url
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $itemProduct
     * @return string
     */
    public function getItemUrl(Mage_Sales_Model_Order_Item $item, Mage_Catalog_Model_Product $itemProduct)
    {
        $productId = $this->_getIdToUse($item, $itemProduct);        
        return Mage::helper('bronto_common/product')->getProductAttribute($productId, 'url');
    }
    
    /**
     * Get Item image
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $itemProduct
     * @return string
     */
    public function getItemImg(Mage_Sales_Model_Order_Item $item, Mage_Catalog_Model_Product $itemProduct)
    {
        if (Mage::helper('bronto_common/product')->getProductAttribute($itemProduct->getId(), 'image')) {
            return Mage::helper('bronto_common/product')->getProductAttribute($itemProduct->getId(), 'image');
        }
        
        $productId = $this->_getIdToUse($item, $itemProduct, false); 
        return Mage::helper('bronto_common/product')->getProductAttribute($productId, 'image');
    }
    
    /**
     * Get the product ID to use based on Item visibility
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $itemProduct
     * @param boolean $checkVisible
     * @return int
     */
    protected function _getIdToUse(Mage_Sales_Model_Order_Item $item, Mage_Catalog_Model_Product $itemProduct, $checkVisible = true)
    {
        if ($checkVisible && in_array($itemProduct->getVisibility(), array('2', '4'))) {
            return $item->getProductId();
        } else {
            $superProductConfig = $this->_getSuperProductConfig($item);
            if ($superProductConfig && array_key_exists('product_id', $superProductConfig)) {
                return $superProductConfig['product_id'];
            } elseif (method_exists($item, 'getParentItemId')) {
                return $item->getParentItemId();
            } else {
                return $item->getProductId();
            }
        }
    }
    
    /**
     * This function gets the order item's info_buyRequest super_product_config values
     * if they exist
     * @param Mage_Sales_Model_Order_Item $item
     * @return boolean|array
     * @access protected
     */
    protected function _getSuperProductConfig(Mage_Sales_Model_Order_Item $item)
    {
        if (method_exists($item, 'getBuyRequest')) {
            $buyRequest = $item->getBuyRequest()->getData();
        } elseif (method_exists($item, 'getProductOptionByCode')) {
            $buyRequest = $item->getProductOptionByCode('info_buyRequest');
        } elseif (method_exists($item, 'getProductOptions')) {
            $options    = $item->getProductOptions();
            $buyRequest = $options['info_buyRequest'];
        } elseif (method_exists($item, 'getOptionByCode')) {
            $buyRequest = $item->getOptionByCode('info_buyRequest');
        } else {
            
            return false;
        }
        
        if ($buyRequest && array_key_exists('super_product_config', $buyRequest)) {
            return $buyRequest['super_product_config'];
        } elseif ($buyRequest && array_key_exists('product', $buyRequest)) {
            return array('product_id' => $buyRequest['product']);
        }
    }
    
    /**
     * Get Orders which aren't in contact queue
     * @param boolean $getCount
     * @return int|Mage_Sales_Model_Resource_Order_Collection
     */
    public function getMissingOrders($getCount = false)
    {
        $queue = Mage::getModel('bronto_order/queue');
        $ids   = $queue->getExistingIds();

        // If just getting count, don't limit results
        if ($getCount) { 
            $limit = false; 
        } else { 
            $limit = 250;
        }
        
        // Get Collection of active orders not already in queue
        /* @var Mage_Sales_Model_Resource_Order_Collection */
        $orders = $queue->getMissingOrders($ids, $limit);
        
        if ($getCount) {
            return $orders->count();
        }
        
        return $orders;
    }
}
