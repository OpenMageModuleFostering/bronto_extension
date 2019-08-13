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
        if (($quoteId > 0) && ($orderId > 0)) {
            $collection->getSelect()->where("`quote_id` = $quoteId OR `order_id` = $orderId");
        } elseif (($quoteId > 0)) {
            $collection->addFieldToFilter('quote_id', $quoteId);
        } elseif (($orderId > 0)) {
            $collection->addFieldToFilter('order_id', $orderId);
        }
        $collection->addFieldToFilter('store_id', $storeId);
        
        try {
            // Handle Results
            if ($collection->count() == 1) {
                $order = $collection->getFirstItem();
                if (($quoteId > 0)) {
                    $order->setQuoteId($quoteId);
                }
                if (($orderId > 0)) {
                    $order->setOrderId($orderId);
                }
                $order->save();

                return $order;
            } else {
                if (($quoteId > 0)) {
                    $this->setQuoteId($quoteId);
                }
                if (($orderId > 0)) {
                    $this->setOrderId($orderId);
                }

                $this->setStoreId($storeId);
            }
        } catch (Exception $e) {
            Mage::helper('bronto_order')->writeDebug("Exception Thrown pulling order row");
        }
        
        return $this;
    }
    
    public function getExistingIds()
    {
        $collection = $this->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('order_id')
            ->group(array('order_id'));
        
        return $collection->getColumnValues('order_id');
    }
    
    /**
     * Get collection of orders which aren't already in the queue, but should be
     * @param array $existingIds
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getMissingOrders($existingIds = array(), $count = 250)
    {
        $orders = Mage::getModel('sales/order')
            ->getCollection();
        
        // If there are existing IDs, don't pull those orders
        if (count($existingIds) > 0) {
            $orders->addFieldToFilter('entity_id', array('nin' => $existingIds));
        }
        
        // If there is a count limit, limit to that many results
        if ($count) {
            $orders->getSelect()->limit($count);
        }
        
        return $orders;
    }
}