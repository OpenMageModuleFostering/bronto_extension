<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 */
class Bronto_Common_Helper_Salesrule extends Bronto_Common_Helper_Data
{
    /**
     * Load Sales Rule by ID
     *
     * @param $ruleId
     *
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getRuleById($ruleId)
    {
        try {
            $rule = Mage::getModel('salesrule/rule')->load($ruleId);
        } catch (Exception $e) {
            $this->writeError('Failed loading Rule for ID: ' . $ruleId);

            return false;
        }

        return $rule;
    }

    /**
     * Retrieve Option array of Sales Rules
     *
     * @return array
     */
    public function getRuleOptionsArray()
    {
        $options = array();

        /** @var Mage_SalesRule_Model_Resource_Rule_Collection $rules */
        $rules = Mage::getModel('salesrule/rule')->getCollection();

        // If there are any rules
        if ($rules->count()) {
            // Cycle Through Rules
            foreach ($rules as $rule) {
                // If rule is not active, the from date or to date are invalid, or rule doesn't have a coupon just skip this rule
                if (
                    !$rule->getIsActive() ||
                    (!is_null($rule->getFromDate()) && $rule->getFromDate() > Mage::getModel('core/date')->date('Y-m-d')) ||
                    (!is_null($rule->getToDate()) && $rule->getToDate() < Mage::getModel('core/date')->date('Y-m-d')) ||
                    ($rule->getCouponType() == Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON)
                ) {
                    continue;
                }

                // Handle Coupon Label
                $couponLabel = '(Coupon: *Auto Generated*)';
                if ($couponCode = $rule->getPrimaryCoupon()->getCode()) {
                    $couponLabel = "(Coupon: {$couponCode})";
                }

                // Build Option
                $options[] = array(
                    'label' => "{$rule->getName()} {$couponLabel}",
                    'value' => $rule->getRuleId(),
                );
            }
        }

        // Add -- None Selected -- Option
        array_unshift($options, array(
            'label' => '-- None Selected --',
            'value' => ''
        ));

        return $options;
    }
}
