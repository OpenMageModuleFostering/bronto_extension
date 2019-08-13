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
    
    public function getExistingIds()
    {
        $collection = $this->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('customer_id')
            ->group(array('customer_id'));
        
        return $collection->getColumnValues('customer_id');
    }
    
    /**
     * Get collection of customers who aren't already in the queue, but should be
     * @param array $existingIds
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    public function getMissingCustomers($existingIds = array(), $count = 250)
    {
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('bronto_imported');
        
        // Only pull active users
        $customers->getSelect()->where('is_active = 1');
        
        // If there are existing IDs, don't pull those customers
        if (count($existingIds) > 0) {
            $customers->addFieldToFilter('entity_id', array('nin' => $existingIds));
        }
        
        // If there is a count limit, limit to that many results
        if ($count) {
            $customers->getSelect()->limit($count);
        }
        
        return $customers;
    }
}