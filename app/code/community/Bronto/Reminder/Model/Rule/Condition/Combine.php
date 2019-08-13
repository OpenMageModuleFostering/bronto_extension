<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Model_Rule_Condition_Combine extends Bronto_Reminder_Model_Condition_Combine_Abstract
{
    /**
     * Intialize model
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('bronto_reminder/rule_condition_combine');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = array(
            array(
                'value' => 'bronto_reminder/rule_condition_wishlist',
                'label' => Mage::helper('bronto_reminder')->__('Wishlist')),
            array(
                'value' => 'bronto_reminder/rule_condition_cart',
                'label' => Mage::helper('bronto_reminder')->__('Shopping Cart')),
        );

        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }
}
