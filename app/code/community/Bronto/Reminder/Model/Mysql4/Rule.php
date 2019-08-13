<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Rule websites table name
     *
     * @var string
     */
    protected $_websiteTable;

    protected function _construct()
    {
        $this->_init('bronto_reminder/rule', 'rule_id');
        $this->_websiteTable = $this->getTable('bronto_reminder/website');
    }

    /**
     * Get empty select object
     *
     * @return Varien_Db_Select
     */
    public function createSelect()
    {
        return $this->_getReadAdapter()->select();
    }

    /**
     * Quote parameters into condition string
     *
     * @param string $string
     * @param string |       array $param
     * @return string
     */
    public function quoteInto($string, $param)
    {
        return $this->_getReadAdapter()->quoteInto($string, $param);
    }

    /**
     * Prepare object data for saving
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getActiveFrom()) {
            $object->setActiveFrom(new Zend_Db_Expr('NULL'));
        } else {
            if ($object->getActiveFrom() instanceof Zend_Date) {
                $object->setActiveFrom($object->getActiveFrom()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
            }
        }

        if (!$object->getActiveTo()) {
            $object->setActiveTo(new Zend_Db_Expr('NULL'));
        } else {
            if ($object->getActiveTo() instanceof Zend_Date) {
                $object->setActiveTo($object->getActiveTo()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
            }
        }
        parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $rule
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $rule)
    {
        if ($rule->hasData('website_ids')) {
            $this->_saveWebsiteIds($rule);
        }
        if ($rule->hasData('store_messages')) {
            $this->_saveMessageData($rule);
        }
        return parent::_afterSave($rule);
    }

    /**
     * Save all website ids associated to rule
     *
     * @param $rule
     * @return Bronto_Reminder_Model_Mysql4_Rule
     */
    protected function _saveWebsiteIds($rule)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->_websiteTable, array('rule_id=?' => $rule->getId()));
        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = array($websiteIds);
        }

        foreach ($websiteIds as $websiteId) {
            $adapter->insert($this->_websiteTable, array(
                'website_id' => $websiteId,
                'rule_id' => $rule->getId()
            ));
        }
        return $this;
    }

    /**
     * Get website ids associated to the rule id
     *
     * @param int $ruleId
     * @return array
     */
    public function getWebsiteIds($ruleId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->_websiteTable, 'website_id')
            ->where('rule_id=?', $ruleId);

        $websiteIds = $this->_getReadAdapter()->fetchCol($select);
        array_unshift($websiteIds, "0");
        return $websiteIds;
    }

    /**
     * Save store Messages
     *
     * @param $rule
     * @return Bronto_Reminder_Model_Mysql4_Rule
     */
    protected function _saveMessageData($rule)
    {
        $adapter = $this->_getWriteAdapter();
        $messageTable = $this->getTable('bronto_reminder/message');
        $adapter->delete($messageTable, array('rule_id = ?' => $rule->getId()));

        $labels = $rule->getStoreLabels();
        $descriptions = $rule->getStoreDescriptions();

        foreach ($rule->getStoreMessages() as $storeId => $messageId) {
            if (!$messageId) {
                continue;
            }
            $adapter->insert($messageTable, array(
                'rule_id' => $rule->getId(),
                'store_id' => $storeId,
                'message_id' => $messageId,
                'label' => $labels[$storeId],
                'description' => $descriptions[$storeId]
            ));
        }
        return $this;
    }

    /**
     * Get Message data assigned to reminder rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getMessageData($ruleId)
    {
        $messageTable = $this->getTable('bronto_reminder/message');
        $select = $this->createSelect()
            ->from($messageTable, array('store_id', 'message_id', 'label', 'description'))
            ->where('rule_id = ?', $ruleId);
        return $this->_getReadAdapter()->fetchAll($select);
    }

    /**
     * Get store data (labels and descriptions) assigned to reminder rule.
     * If labels and descriptions are not specified it will be replaced with default values.
     *
     * @param int $ruleId
     * @param int $storeId
     * @return array
     */
    public function getStoreMessageData($ruleId, $storeId)
    {
        $messageTable = $this->getTable('bronto_reminder/message');
        $ruleTable = $this->getTable('bronto_reminder/rule');

        $select = $this->createSelect()->from(array('m' => $messageTable),
            'm.message_id,
            IF(m.label != \'\', m.label, r.default_label) as label,
            IF(m.description != \'\', m.description, r.default_description) as description'
        );

        $select->join(
            array('r' => $ruleTable),
            'r.rule_id = m.rule_id',
            array()
        );

        $select->where('m.rule_id = ?', $ruleId);
        $select->where('m.store_id = ?', $storeId);

        return $this->_getReadAdapter()->fetchRow($select);
    }

    /**
     * @param int $ruleId
     * @param int $storeId
     * @param int $customerId
     * @param string $messageId
     * @return array
     */
    public function getRuleLogItemsData($ruleId, $storeId, $customerId, $messageId = null)
    {
        $couponTable = $this->getTable('bronto_reminder/coupon');
        $logTable = $this->getTable('bronto_reminder/log');

        $select = $this->createSelect()->from(array('l' => $logTable));
        $select->joinInner(
            array('c' => $couponTable),
            "c.rule_id = {$ruleId} AND c.store_id = {$storeId} AND c.customer_id = {$customerId} AND c.unique_id = l.unique_id",
            array()
        );
        $select->where('l.rule_id = ?', $ruleId);
        if (!empty($messageId)) {
            $select->where('l.bronto_message_id = ?', $messageId);
        }
        $select->order('l.sent_at DESC');
        $select->limit(1);

        return $this->_getReadAdapter()->fetchRow($select);
    }

    /**
     * Get comparison condition for rule condition operator which will be used in SQL query
     *
     * @param string $operator
     * @return string
     */
    public function getSqlOperator($operator)
    {
        switch ($operator) {
            case '==':
                return '=';
            case '!=':
                return '<>';
            case '{}':
                return 'LIKE';
            case '!{}':
                return 'NOT LIKE';
            case 'between':
                return "BETWEEN '%s' AND '%s'";
            case '>':
            case '<':
            case '>=':
            case '<=':
                return $operator;
            default:
                Mage::throwException(Mage::helper('bronto_reminder')->__('Unknown operator specified.'));
        }
    }

    /**
     * Create string for select "where" condition based on field name, comparison operator and vield value
     *
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return string
     */
    public function createConditionSql($field, $operator, $value)
    {
        $sqlOperator = $this->getSqlOperator($operator);
        $condition = '';
        switch ($operator) {
            case '{}':
            case '!{}':
                if (is_array($value)) {
                    if (!empty($value)) {
                        $sqlOperator = ($operator == '{}') ? 'IN' : 'NOT IN';
                        $condition = $this->quoteInto($field . ' ' . $sqlOperator . ' (?)', $value);
                    }
                } else {
                    $condition = $this->quoteInto($field . ' ' . $sqlOperator . ' ?', '%' . $value . '%');
                }
                break;
            case 'between':
                $condition = $field . ' ' . sprintf($sqlOperator, $value['start'], $value['end']);
                break;
            default:
                $condition = $this->quoteInto($field . ' ' . $sqlOperator . ' ?', $value);
                break;
        }
        return $condition;
    }

    /**
     * Deactivate already matched customers before new matching process
     *
     * @param int $ruleId
     * @return Bronto_Reminder_Model_Mysql4_Rule
     */
    public function deactivateMatchedCustomers($ruleId)
    {
        $this->_getWriteAdapter()->update(
            $this->getTable('bronto_reminder/coupon'),
            array('is_active' => '0'),
            array('rule_id = ?' => $ruleId)
        );
        return $this;
    }

    /**
     * Deactivate customers that have been matched and emailed
     *
     * @param string $uniqueId
     * @return Bronto_Reminder_Model_Mysql4_Rule
     */
    public function deactivateMatchedCustomer($uniqueId)
    {
        $this->_getWriteAdapter()->update(
            $this->getTable('bronto_reminder/coupon'),
            array('is_active' => '0'),
            array('unique_id = ?' => $uniqueId)
        );
        return $this;
    }

    /**
     * Additional debugging that shows query and parameter values
     * @param string $sql
     * @param array $bind
     */
    public function logFullQuery($sql, $bind)
    {
        foreach ($bind as $var => $val) {
            $sql = str_replace(':' . $var, $val, $sql);
        }
        Mage::helper('bronto_reminder')->writeDebug('Full Query: ' . $sql, 'bronto_reminder_sql.log');
    }

    /**
     * Try to associate reminder rule with matched customers.
     * If customer was added earlier, update is_active column.
     *
     * @param Bronto_Reminder_Model_Rule $rule
     * @param Mage_SalesRule_Model_Rule $salesRule
     * @param int $websiteId
     * @return Bronto_Reminder_Model_Mysql4_Rule
     */
    public function saveMatchedCustomers($rule, $salesRule, $websiteId, $threshold = null)
    {
        $select = $rule->getConditions()->getConditionsSql($rule, $websiteId);
        $interval = Mage::helper('bronto_reminder')->getCronInterval();

        if (!$rule->getConditionSql()) {
            return $this;
        }

        if ($threshold) {
            $select->where('c.emails_failed IS NULL OR c.emails_failed < ? ', $threshold);
        }

        // Only pull for reminders not already attached to an active record
        $select->where('c.is_active IS NULL OR c.is_active <> 1');

        $i = 0;
        $ruleId = $rule->getId();
        $adapter = $this->_getWriteAdapter();
        $currentDate = $this->formatDate(time());
        $dataToInsert = array();
        Mage::helper('bronto_reminder')->writeDebug('ruleId: ' . $rule->getId() . ' website: ' . $websiteId, 'bronto_reminder_sql.log');
        # Replaced by logFullQuery below
        #Mage::helper('bronto_reminder')->writeDebug($select->__toString(), 'bronto_reminder_sql.log');

        // Log the query with binds replaced
        $this->logFullQuery($select, array('rule_id' => $ruleId, 'interval' => $interval));

        /* @var $stmt Varien_Db_Statement_Pdo_Mysql */
        $stmt = $adapter->query($select, array('rule_id' => $ruleId, 'interval' => $interval));
        Mage::helper('bronto_reminder')->writeDebug('saveMatchedCustomers():', 'bronto_reminder_sql.log');

        try {
            $adapter->beginTransaction();
            while ($row = $stmt->fetch()) {
                if (empty($row['coupon_id']) && $salesRule) {
                    $coupon = $salesRule->acquireCoupon();
                    $couponId = ($coupon !== null) ? $coupon->getId() : null;
                } else {
                    $couponId = $row['coupon_id'];
                }

                $dataToInsert[] = array(
                    'rule_id' => $ruleId,
                    'coupon_id' => $couponId,
                    'unique_id' => $row['unique_id'],
                    'store_id' => $row['store_id'],
                    'customer_id' => $row['customer_id'],
                    'quote_id' => $row['quote_id'],
                    'wishlist_id' => $row['wishlist_id'],
                    'visitor_id' => $row['visitor_id'],
                    'associated_at' => $currentDate,
                    'is_active' => '1'
                );
                $i++;

                if (($i % 1000) == 0) {
                    $this->_saveMatchedCustomerData($dataToInsert);
                    $adapter->commit();
                    $adapter->beginTransaction();
                    $dataToInsert = array();
                }
            }

            $this->_saveMatchedCustomerData($dataToInsert);
            $adapter->commit();

            Mage::helper('bronto_reminder')->writeDebug("  Query Matched {$i} customers", 'bronto_reminder_sql.log');
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Save data by matched customer coupons
     *
     * @param array $data
     */
    protected function _saveMatchedCustomerData($data)
    {
        if ($data) {
            $table = $this->getTable('bronto_reminder/coupon');
            $this->_getWriteAdapter()->insertOnDuplicate($table, $data, array('is_active'));
        }
    }

    /**
     * Return list of customers for notification process.
     * This process can be initialized system cron or by admin for some rule
     *
     * @param int|null $limit
     * @param int|null $ruleId
     * @return array
     */
    public function getCustomersForNotification($limit = null, $ruleId = null)
    {
        $couponTable = $this->getTable('bronto_reminder/coupon');
        $ruleTable = $this->getTable('bronto_reminder/rule');
        $logTable = $this->getTable('bronto_reminder/log');
        $currentDate = $this->formatDate(time());

        $select = $this->createSelect()->from(
            array('c' => $couponTable),
            array('rule_id', 'coupon_id', 'unique_id', 'store_id', 'customer_id', 'quote_id', 'wishlist_id', 'visitor_id')
        );


        $select->joinLeft(
            array('l' => $logTable),
            'c.rule_id=l.rule_id AND c.unique_id=l.unique_id',
            array()
        );

        $select->join(
            array('r' => $ruleTable),
            'c.rule_id = r.rule_id AND r.is_active = 1',
            array('schedule')
        );

        if ($ruleId) {
            $select->where('c.rule_id = ?', $ruleId);
        }

        $select->where('c.is_active = 1');
        $select->group(array('c.unique_id', 'c.rule_id'));
        $select->having("(MAX(l.sent_at) IS NULL) OR (FIND_IN_SET(TO_DAYS('{$currentDate}') - TO_DAYS(MIN(l.sent_at)), r.schedule))");

        if ($limit) {
            $select->limit($limit);
        }

        /**
        SELECT `c`.`customer_id`, `c`.`coupon_id`, `c`.`rule_id`, `r`.`schedule`
        FROM `bronto_reminder_rule_coupon` AS `c`
        INNER JOIN `bronto_reminder_rule` AS `r` ON c.rule_id = r.rule_id AND r.is_active = 1
        LEFT JOIN `bronto_reminder_rule_log` AS `l` ON c.rule_id=l.rule_id AND c.customer_id=l.customer_id
        WHERE (c.rule_id = '1') AND (c.is_active = 1)
        GROUP BY `c`.`customer_id`, `c`.`rule_id`
        HAVING ((MAX(l.sent_at) IS NULL)
        OR (FIND_IN_SET(TO_DAYS('2012-05-11 09:21:27') - TO_DAYS(MIN(l.sent_at)), r.schedule)
        AND TO_DAYS('2012-05-11 09:21:27') != TO_DAYS(MAX(l.sent_at))))
        LIMIT 100
         */

        return $this->_getReadAdapter()->fetchAll($select);
    }

    /**
     * Add notification log row after letter was successfully sent.
     *
     * @param int $ruleId
     * @param int $customerId
     * @return Bronto_Reminder_Model_Mysql4_Rule
     */
    public function addNotificationLog($ruleId, $uniqueId, $deliveryId = null, $messageId = null)
    {
        $data = array(
            'rule_id' => $ruleId,
            'unique_id' => $uniqueId,
            'sent_at' => $this->formatDate(time()),
            'bronto_delivery_id' => $deliveryId,
            'bronto_message_id' => $messageId,
        );

        $this->_getWriteAdapter()->insert($this->getTable('bronto_reminder/log'), $data);
        return $this;
    }

    /**
     * Update failed email counter.
     *
     * @param int $ruleId
     * @param int $uniqueId
     * @return Bronto_Reminder_Model_Mysql4_Rule
     */
    public function updateFailedEmailsCounter($ruleId, $uniqueId)
    {
        $this->_getWriteAdapter()->update($this->getTable('bronto_reminder/coupon'),
            array('emails_failed' => new Zend_Db_Expr('emails_failed + 1')),
            array('rule_id = ?' => $ruleId, 'unique_id = ?' => $uniqueId)
        );

        return $this;
    }

    /**
     * Return count of reminder rules assigned to specified sales rule.
     *
     * @param int $salesruleId
     * @return int
     */
    public function getAssignedRulesCount($salesruleId)
    {
        $select = $this->createSelect()->from(
            array('r' => $this->getTable('bronto_reminder/rule')),
            array(new Zend_Db_Expr('count(*)'))
        );
        $select->where('r.salesrule_id = ?', $salesruleId);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Remove row from coupon table by column, value and store_id
     *
     * @param type $column
     * @param type $value
     * @return Bronto_Reminder_Model_Mysql4_Rule
     */
    public function removeFromReminders($column, $value)
    {
        $where = "$column = $value";
        $this->_getWriteAdapter()->delete($this->getTable('bronto_reminder/coupon'), $where);

        return $this;
    }
}
