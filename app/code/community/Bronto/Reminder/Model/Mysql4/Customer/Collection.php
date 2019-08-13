<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_Mysql4_Customer_Collection extends Mage_Customer_Model_Entity_Customer_Collection
{
    /**
     * Instantiate select to get matched customers
     *
     * @return Bronto_Reminder_Model_Mysql4_Customer_Collection
     */
    protected function _initSelect()
    {
        $rule   = Mage::registry('current_reminder_rule');
        $select = $this->getSelect();

        $customerTable = $this->getTable('customer/entity');
        $couponTable   = $this->getTable('bronto_reminder/coupon');
        $logTable      = $this->getTable('bronto_reminder/log');
        // Used for gathering guest data
        $quoteTable    = $this->getResource()->getTable('sales/quote');
        $logQuoteTable = Mage::getResourceSingleton('log/log')->getTable('log/quote_table');

        try {
            $salesRuleCouponTable = $this->getTable('salesrule/coupon');
        } catch (Exception $e) {
            $salesRuleCouponTable = false;
        }

        $select->from(array('c' => $couponTable), array('store_id', 'unique_id', 'customer_id', 'visitor_id', 'associated_at', 'emails_failed', 'is_active'));
        $select->where('c.rule_id = ?', $rule->getId());

        // Select Guest Data
        $guestSelect = $this->getConnection()->select();
        $guestSelect->from(
            array('q' => $quoteTable),
            array('email' => 'q.customer_email')
        )->where('lq.visitor_id = c.visitor_id');
        $guestSelect->joinInner(
            array('lq' => $logQuoteTable),
            'q.entity_id = lq.quote_id',
            array()
        );

        $select->joinLeft(
            array('e' => $customerTable),
            'e.entity_id = c.customer_id',
            array(
                'entity_id',
                'email' => new Zend_Db_Expr("IF(e.email IS NULL, ($guestSelect), e.email)")
            )
        );

        $subSelect = $this->getConnection()->select();
        $subSelect->from(array('g' => $logTable), array(
            'unique_id',
            'rule_id',
            'emails_sent' => new Zend_Db_Expr('COUNT(log_id)'),
            'last_sent' => new Zend_Db_Expr('MAX(sent_at)')
        ));

        $subSelect->where('rule_id = ?', $rule->getId());
        $subSelect->group(array('unique_id', 'rule_id'));

        $select->joinLeft(
            array('l' => $subSelect),
            'l.rule_id = c.rule_id AND l.unique_id = c.unique_id',
            array('l.emails_sent', 'l.last_sent')
        );

        if ($salesRuleCouponTable) {
            $select->joinLeft(
                array('sc' => $salesRuleCouponTable),
                'sc.coupon_id = c.coupon_id',
                array('code', 'usage_limit', 'usage_per_customer')
            );
        }

        $this->_joinFields['associated_at'] = array('table' => 'c', 'field' => 'associated_at');
        $this->_joinFields['emails_failed'] = array('table' => 'c', 'field' => 'emails_failed');
        $this->_joinFields['is_active'] = array('table' => 'c', 'field' => 'is_active');

        if ($salesRuleCouponTable) {
            $this->_joinFields['code'] = array('table' => 'sc', 'field' => 'code');
            $this->_joinFields['usage_limit'] = array('table' => 'sc', 'field' => 'usage_limit');
            $this->_joinFields['usage_per_customer'] = array('table' => 'sc', 'field' => 'usage_per_customer');
        }

        $this->_joinFields['emails_sent'] = array('table' => 'l', 'field' => 'emails_sent');
        $this->_joinFields['last_sent'] = array('table' => 'l', 'field' => 'last_sent');

        return $this;
    }
}
