<?php

/**
 * @package     Bronto\Customer
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.5
 */
class Bronto_Customer_Model_Resource_Customer_Collection
    extends Mage_Customer_Model_Entity_Customer_Collection
{
    /**
     * @return Bronto_Customer_Model_Resource_Customer_Collection
     */
    public function addBrontoImportedFilter()
    {
        $this->addAttributeToFilter('bronto_imported', array('notnull' => true));
        return $this;
    }

    /**
     * @return Bronto_Customer_Model_Resource_Customer_Collection
     */
    public function addBrontoNotImportedFilter()
    {
        $this->addFieldToFilter('bronto_imported', array('null' => true));
        return $this;
    }

    public function addBrontoMissingImportedAttribute()
    {
        $resource = Mage::getSingleton('core/resource');
        $db = $resource->getConnection('core_read');

        $attributeId = Mage::getModel('eav/config')
            ->getAttribute('customer', 'bronto_imported')
            ->getId();

        $subSelect = $db->select()
            ->from($resource->getTableName('customer_entity_datetime'), array('entity_id'))
            ->where($db->quoteInto('`attribute_id` = ?', $attributeId));
        $this->getSelect()
            ->where($db->quoteInto('`e`.`entity_id` not in ?', $subSelect));

        return $this;
    }

    /**
     * @param mixed $storeIds (null, int|string, array, array may contain null)
     * @return Bronto_Customer_Model_Resource_Customer_Collection
     */
    public function addStoreFilter($storeIds)
    {
        $nullCheck = false;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        $storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];

        if ($nullCheck) {
            $this->getSelect()->where('store_id IN(?) OR store_id IS NULL', $storeIds);
        } else {
            $this->getSelect()->where('store_id IN(?)', $storeIds);
        }

        return $this;
    }

    /**
     * Sort order by order created_at date
     *
     * @param string $dir
     * @return Bronto_Customer_Model_Resource_Customer_Collection
     */
    public function orderByCreatedAt($dir = self::SORT_ORDER_DESC)
    {
        $this->setOrder('created_at', $dir);
        return $this;
    }

    /**
     * Sort order by order updated_at date
     *
     * @param string $dir
     * @return Bronto_Customer_Model_Resource_Customer_Collection
     */
    public function orderByUpdatedAt($dir = self::SORT_ORDER_DESC)
    {
        $this->setOrder('updated_at', $dir);
        return $this;
    }
}
