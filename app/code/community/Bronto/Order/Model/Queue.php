<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.7
 */
class Bronto_Order_Model_Queue extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('bronto_order/queue');
    }
    
    /**
     * Retrieve Order Queue Row
     * @param int $orderId
     * @param int $quoteId
     * @param int $storeId
     * @return Bronto_Order_Model_Queue
     */
    public function getOrderRow($orderId = false, $quoteId = false, $storeId = false)
    {
        // Either OrderID or QuoteID must be present as well as StoreID
        if ((false === $orderId && false === $quoteId) || false === $storeId) {
            return $this;
        }
        
        // Create Collection
        $collection = $this->getCollection();
        
        // Add Filters
        if ($orderId) {
            $collection->addFieldToFilter('order_id', $orderId);
        }
        if ($quoteId) {
            $collection->addFieldToFilter('quote_id', $quoteId);
        }
        $collection->addFieldToFilter('store_id', $storeId);
        
        // Handle Results
        if ($collection->count() == 1) {
            return $collection->getFirstItem();
        } else {
            if ($orderId) {
                $this->setOrderId($orderId);
            }
            if ($quoteId) {
                $this->setQuoteId($quoteId);
            }
            
            $this->setStoreId($storeId);
        }
        
        return $this;
    }
}