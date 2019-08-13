<?php

/**
 * @package   Bronto\Customer
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.0.2
 */
class Bronto_Customer_Model_Queue extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('bronto_customer/queue');
    }
    
    /**
     * Retrieve Customer Queue Row
     * @param int $customerId
     * @param int $storeId
     * @return Bronto_Customer_Model_Queue
     */
    public function getCustomerRow($customerId, $storeId)
    {
        // Create Collection
        $collection = $this->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('store_id', $storeId);
        
        // Handle Results
        if ($collection->count() == 1) {
            return $collection->getFirstItem();
        } else {
            $this->setCustomerId($customerId)
                 ->setStoreId($storeId);
        }
        
        return $this;
    }
}