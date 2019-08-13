<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_Rule_Condition_Wishlist extends Bronto_Reminder_Model_Condition_Combine_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('bronto_reminder/rule_condition_wishlist');
        $this->setValue(null);
    }

    /**
     * Get list of available subconditions
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return Mage::getModel('bronto_reminder/rule_condition_wishlist_combine')->getNewChildSelectOptions();
    }

    /**
     * Get input type for attribute value
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Override parent method
     *
     * @return Bronto_Reminder_Model_Rule_Condition_Wishlist
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array());
        return $this;
    }

    /**
     * Prepare operator select options
     *
     * @return Bronto_Reminder_Model_Rule_Condition_Wishlist
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '==' => Mage::helper('rule')->__('for'),
            '>'  => Mage::helper('rule')->__('for greater than'),
            '>=' => Mage::helper('rule')->__('for or greater than')
        ));
        return $this;
    }

    /**
     * Return required validation
     *
     * @return true
     */
    protected function _getRequiredValidation()
    {
        return true;
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('bronto_reminder')->__('Wishlist is not empty and abandoned %s %s days and %s of these conditions match:',
                $this->getOperatorElementHtml(),
                $this->getValueElementHtml(),
                $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

     /**
     * Get condition SQL select
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($customer, $website)
    {
        $conditionValue = (int)$this->getValue();
        if ($conditionValue < 1) {
            Mage::throwException(Mage::helper('bronto_reminder')->__('Root wishlist condition should have days value at least 1.'));
        }

        $wishlistTable = $this->getResource()->getTable('wishlist/wishlist');
        $wishlistItemTable = $this->getResource()->getTable('wishlist/item');
        $operator = $this->getResource()->getSqlOperator($this->getOperator());

        $select = $this->getResource()->createSelect();
        $select->from(array('item' => $wishlistItemTable), array(new Zend_Db_Expr(1)));

        $select->joinInner(
            array('list' => $wishlistTable),
            'item.wishlist_id = list.wishlist_id',
            array()
        );

        $this->_limitByStoreWebsite($select, $website, 'item.store_id');
        $select->where("UNIX_TIMESTAMP('" . now() . "' - INTERVAL ? DAY) {$operator} UNIX_TIMESTAMP(list.updated_at)", $conditionValue);
        $select->where($this->_createCustomerFilter($customer, 'list.customer_id'));
        $select->limit(1);
        return $select;
    }

    /**
     * Get base SQL select
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    public function getConditionsSql($customer, $website)
    {
        $select     = $this->_prepareConditionsSql($customer, $website);
        $required   = $this->_getRequiredValidation();
        $aggregator = ($this->getAggregator() == 'all') ? ' AND ' : ' OR ';
        $operator   = $required ? '=' : '<>';
        $conditions = array();

        foreach ($this->getConditions() as $condition) {
            if ($sql = $condition->getConditionsSql($customer, $website)) {
                $conditions[] = "(IFNULL(($sql), 0) {$operator} 1)";
            }
        }

        if (!empty($conditions)) {
            $select->where(implode($aggregator, $conditions));
        }

        return $select;
    }
}
