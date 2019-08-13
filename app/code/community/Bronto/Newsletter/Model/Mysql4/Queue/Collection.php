<?php

/**
 * @package   Bronto\Newsletter
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.3.5
 */
class Bronto_Newsletter_Model_Mysql4_Queue_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('bronto_newsletter/queue');
    }


    /**
     * @return Bronto_Newsletter_Model_Mysql4_Queue_Collection
     */
    public function addBrontoImportedFilter()
    {
        $this->addFieldToFilter('imported', array('eq' => '1'));
        return $this;
    }

    /**
     * @return Bronto_Order_Model_Mysql4_Queue_Collection
     */
    public function addBrontoSuppressedFilter()
    {
        $this->addFieldToFilter('bronto_suppressed', array('notnull' => true));
        return $this;
    }

    /**
     * @return Bronto_Order_Model_Mysql4_Queue_Collection
     */
    public function addBrontoNotSuppressedFilter()
    {
        $this->addFieldToFilter('bronto_suppressed', array('null' => true));
        return $this;
    }

    /**
     * @return Bronto_Newsletter_Model_Mysql4_Queue_Collection
     */
    public function addBrontoNotImportedFilter()
    {
        $this->addFieldToFilter('imported', array('neq' => '1'));
        return $this;
    }

    /**
     * @param mixed $storeIds (null, int|string, array, array may contain null)
     * @return Bronto_Newsletter_Model_Mysql4_Queue_Collection
     */
    public function addStoreFilter($storeIds)
    {
        $nullCheck = false;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds, true)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $this->getSelect()->where('store IN(?) OR store IS NULL', $storeIds);
        } else {
            $this->getSelect()->where('store IN(?)', $storeIds);
        }

        return $this;
    }
}