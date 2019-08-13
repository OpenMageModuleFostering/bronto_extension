<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_Rule_Condition_Cart extends Bronto_Reminder_Model_Condition_Combine_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('bronto_reminder/rule_condition_cart');
        $this->setValue(null);
    }

    /**
     * Get list of available subconditions
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return Mage::getModel('bronto_reminder/rule_condition_cart_combine')->getNewChildSelectOptions();
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
     * @return Bronto_Reminder_Model_Rule_Condition_Cart
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array());
        return $this;
    }

    /**
     * Prepare operator select options
     *
     * @return Bronto_Reminder_Model_Rule_Condition_Cart
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '==' => Mage::helper('rule')->__('for'),
            '>'  => Mage::helper('rule')->__('for greater than'),
            '>=' => Mage::helper('rule')->__('for or greater than'),
            '<'  => Mage::helper('rule')->__('for less than'),
            '<=' => Mage::helper('rule')->__('for or less than'),
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
     * Init available options list
     *
     * @return Bronto_Reminder_Model_Rule_Condition_Cart_Amount
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array(
            'days'    => Mage::helper('bronto_reminder')->__('days'),
            'hours'   => Mage::helper('bronto_reminder')->__('hours'),
            'minutes' => Mage::helper('bronto_reminder')->__('minutes')
        ));
        return $this;
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('bronto_reminder')->__('Shopping cart is not empty and abandoned %s %s %s and %s of these conditions match:',
                $this->getOperatorElementHtml(),
                $this->getValueElementHtml(),
                $this->getAttributeElementHtml(),
                $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * Get condition SQL select
     *
     * @param int|Zend_Db_Expr $customer
     * @param int|Zend_Db_Expr $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($customer, $website)
    {
        $interval       = Mage::helper('bronto_reminder')->getCronInterval();
        $attributeValue = strtolower($this->getAttribute());

        switch ($attributeValue) {
            case 'hours':
                $currentDateStart = now(false);
                $durationSql      = 'HOUR';
                $conditionValue   = (int) $this->getValue();
                if ($conditionValue < 1) {
                    Mage::throwException(Mage::helper('bronto_reminder')->__('Root shopping cart condition should have %s value at least 1.', $attributeValue));
                }
                break;
            case 'minutes':
                $currentDateStart = now(false);
                $durationSql      = 'MINUTE';
                $conditionValue   = (int) $this->getValue();
                if (!Mage::helper('bronto_common')->isTestModeEnabled()) {
                    if ($conditionValue < 30) {
                        Mage::throwException(Mage::helper('bronto_reminder')->__('Root shopping cart condition should have %s value at least 30.', $attributeValue));
                    }
                }
                break;
            case 'days':
            default:
                $currentDateStart = now(true);
                $durationSql      = 'DAY';
                $conditionValue   = (int) $this->getValue();
                if ($conditionValue < 1) {
                    Mage::throwException(Mage::helper('bronto_reminder')->__('Root shopping cart condition should have %s value at least 1.', $attributeValue));
                }
                break;
        }

        if ($conditionValue <= 0) {
            Mage::throwException(Mage::helper('bronto_reminder')->__('Root shopping cart condition should have %s value greater than 0.', $attributeValue));
        }

        $table = $this->getResource()->getTable('sales/quote');
        $operator = $this->getResource()->getSqlOperator($this->getOperator());

        $select = $this->getResource()->createSelect();
        $select->from(array('quote' => $table), array(new Zend_Db_Expr(1)));

        $this->_limitByStoreWebsite($select, $website, 'quote.store_id');

        if ($operator == '=') {
            switch ($attributeValue) {
                case 'hours':
                    // cart + X hour(s) <= [now] <= cart + (X hour(s) * 60) + interval minute(s)
                    // 3 hours: cart + 180 minutes <= [now] <= cart + 195 minutes
                    $conditionValueInMinutes = $conditionValue * 60;
                    $select->where("'{$currentDateStart}' >= DATE_ADD(quote.updated_at, INTERVAL ? HOUR)",   $conditionValue);
                    $select->where("'{$currentDateStart}' <= DATE_ADD(quote.updated_at, INTERVAL ? MINUTE)", $conditionValueInMinutes + $interval);
                    break;
                case 'minutes':
                    // cart + X minute(s) <= [now] <= cart + X minute(s) + interval minute(s)
                    // 60 minutes: cart + 60 minutes <= [now] <= cart + 75 minutes
                    $select->where("'{$currentDateStart}' >= DATE_ADD(quote.updated_at, INTERVAL ? MINUTE)", $conditionValue);
                    $select->where("'{$currentDateStart}' <= DATE_ADD(quote.updated_at, INTERVAL ? MINUTE)", $conditionValue + $interval);
                    break;
                case 'days':
                default:
                    // cart + X day(s) <= [now] <= cart + (X day(s) * 1440) + interval minute(s)
                    // 1 day: cart + 1 day <= [now] <= cart + 1455 minutes
                    $conditionValueInMinutes = $conditionValue * 1440;
                    $select->where("'{$currentDateStart}' >= DATE_ADD(quote.updated_at, INTERVAL ? DAY)",    $conditionValue);
                    $select->where("'{$currentDateStart}' <= DATE_ADD(quote.updated_at, INTERVAL ? MINUTE)", $conditionValueInMinutes + $interval);
                    break;
            }
        } else {
            if ($operator == '>=') {
                if ($conditionValue > 0) {
                    $conditionValue--;
                } else {
                    $currentDateStart = now();
                }
            } elseif ($operator == '<=') {
                if ($conditionValue > 0) {
                    $conditionValue++;
                } else {
                    $currentDateStart = now();
                }
            }

            $select->where("DATE_SUB('{$currentDateStart}', INTERVAL ? {$durationSql}) {$operator} quote.updated_at", $conditionValue);
        }

        $select->where('quote.is_active = 1');
        $select->where('quote.items_count > 0');
        $select->where($this->_createCustomerFilter($customer, 'quote.customer_id'));
        $select->limit(1);

        return $select;
    }

    /**
     * Get base SQL select
     *
     * @param int|Zend_Db_Expr $customer
     * @param int|Zend_Db_Expr $website
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
