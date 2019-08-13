<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_Rule_Condition_Combine_Root extends Bronto_Reminder_Model_Rule_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('bronto_reminder/rule_condition_combine_root');
    }

    /**
     * Prepare base select with limitation by customer
     *
     * @param null             | array | int | Mage_Customer_Model_Customer $customer
     * @param int              | Zend_Db_Expr $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($customer, $website)
    {
        $select = $this->getResource()->createSelect();
        $rootTable = $this->getResource()->getTable('customer/entity');
        $couponTable = $this->getResource()->getTable('bronto_reminder/coupon');

        $select->from(array('root' => $rootTable), array('entity_id'));

        $select->joinLeft(
            array('c' => $couponTable),
            'c.customer_id=root.entity_id AND c.rule_id=:rule_id',
            array('c.coupon_id')
        );

        if ($customer === null) {
            if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
                $select->where('website_id=?', $website);
            }
        }
        return $select;
    }

    /**
     * Get SQL select.
     * Rewrited for cover root conditions combination with additional condition by customer
     *
     * @param Mage_Customer_Model_Customer | Zend_Db_Select | Zend_Db_Expr $customer
     * @param int                          | Zend_Db_Expr $website
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
        else {
            $select->reset();
        }

        return $select;
    }
}
