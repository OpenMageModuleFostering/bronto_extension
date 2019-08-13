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
     * @param null|Bronto_Reminder_Model_Rule        $rule
     * @param int              | Zend_Db_Expr $website
     * @return Varien_Db_Select
     */
    protected function _prepareConditionsSql($rule, $website)
    {
        $select = $this->getResource()->createSelect();
        
        $conditionTypes = array();
        foreach ($this->getConditions() as $condition) {
            $type = explode('_', $condition->getType());            
            $conditionTypes[] = array_pop($type);
        }
        
        // Define Tables
        $customerTable     = $this->getResource()->getTable('customer/entity');
        $quoteTable        = $this->getResource()->getTable('sales/quote');
        $storeTable        = $this->getResource()->getTable('core/store');
        $logQuoteTable     = Mage::getResourceSingleton('log/log')->getTable('log/quote_table');
        $logCustomerTable  = Mage::getResourceSingleton('log/log')->getTable('log/customer');
        $wishlistTable     = $this->getResource()->getTable('wishlist/wishlist');
        $wishlistItemTable = $this->getResource()->getTable('wishlist/item');

        // If conditions are based on Cart or Wishlist
        if (in_array('cart', $conditionTypes) || in_array('wishlist', $conditionTypes)) {    
            $subselect = $this->getResource()->createSelect();
            
            // If conditions are based on Cart and Wishlist
            if (in_array('cart', $conditionTypes) && in_array('wishlist', $conditionTypes)) {
                $groupby = array('quote_id', 'wishlist_id');
                
                $subselect->from(
                    array('quote' => $quoteTable), 
                    array(
                        'quote_id' => 'entity_id',
                        'customer_id' => new Zend_Db_Expr('IF(quote.customer_id IS NULL, IF(wishlist.customer_id IS NULL, 0, wishlist.customer_id), quote.customer_id)')
                    )
                )
                ->where('quote.is_active = ?', 1)
                ->where('quote.items_count > ?', 0)
                ->where('quote.customer_email IS NOT NULL');
                
                $subselect->joinLeft(
                    array('log' => $logQuoteTable),
                    'log.quote_id=quote.entity_id',
                    array('visitor_id' => 'log.visitor_id')
                );
                
                $subselect->joinInner(
                    array('store' => $storeTable), 
                    'quote.store_id=store.store_id', 
                    array('store_id' => 'store.store_id')
                )->where('store.website_id=?', $website);
                
                $subselect->joinLeft(
                    array('wishlist' => $wishlistTable), 
                    'wishlist.customer_id=quote.customer_id', 
                    array('wishlist_id' => 'wishlist.wishlist_id')
                );
                
                $subselect->joinLeft(
                    array('wishlist_item' => $wishlistItemTable), 
                    'wishlist_item.wishlist_id=wishlist.wishlist_id', 
                    array()
                );
                
            } 
            // If conditions are based on Cart Only
            elseif (in_array('cart', $conditionTypes)) {
                $groupby = 'quote_id';
                
                $subselect->from(
                    array('quote' => $quoteTable), 
                    array(
                        'quote_id' => 'entity_id',
                        'customer_id' => new Zend_Db_Expr('IF(quote.customer_id IS NULL, 0, quote.customer_id)'),
                        'wishlist_id' => new Zend_Db_Expr('0'),
                    )
                )
                ->where('quote.is_active = ?', 1)
                ->where('quote.items_count > ?', 0)
                ->where('quote.customer_email IS NOT NULL');
                
                $subselect->joinLeft(
                    array('log' => $logQuoteTable),
                    'log.quote_id=quote.entity_id',
                    array('visitor_id' => 'log.visitor_id')
                );
                
                $subselect->joinInner(
                    array('store' => $storeTable), 
                    'quote.store_id=store.store_id', 
                    array('store_id' => 'store.store_id')
                )->where('store.website_id=?', $website);
            } 
            // If conditions are based on Wishlist Only
            elseif (in_array('wishlist', $conditionTypes)) {
                $groupby = 'wishlist_id';
                
                $subselect->from(
                    array('customer' => $customerTable), 
                    array(
                        'quote_id' => new Zend_Db_Expr('0'),
                        'customer_id' => 'entity_id',
                    )
                );
                
                $subselect->joinLeft(
                    array('wishlist' => $wishlistTable), 
                    'wishlist.customer_id=customer.entity_id', 
                    array('wishlist_id' => 'wishlist.wishlist_id')
                );
                
                $subselect->joinLeft(
                    array('wishlist_item' => $wishlistItemTable), 
                    'wishlist_item.wishlist_id=wishlist.wishlist_id', 
                    array()
                );
                
                $subselect->joinLeft(
                    array('log' => $logCustomerTable),
                    'log.customer_id=customer.entity_id',
                    array('visitor_id' => 'log.visitor_id')
                );
                
                $subselect->joinInner(
                    array('store' => $storeTable), 
                    'wishlist_item.store_id=store.store_id', 
                    array('store_id' => 'store.store_id')
                )->where('store.website_id=?', $website);
            }
            
            // Set main select to pull use subselect as root and pull required fields
            $select->from(array('root' => $subselect), array(
                'unique_id' => new Zend_Db_Expr(
                    "CONCAT(:rule_id, '-', `root`.`store_id`, '-', IF(`root`.`customer_id` IS NULL, 0, `root`.`customer_id`), '-', IF(`root`.`visitor_id` IS NULL, 0, `root`.`visitor_id`))"
                ),
                'store_id', 
                'customer_id' => new Zend_Db_Expr("IF(`root`.`customer_id` IS NULL, 0, `root`.`customer_id`)"), 
                'quote_id'    => new Zend_Db_Expr("IF(`root`.`quote_id` IS NULL, 0, `root`.`quote_id`)"), 
                'wishlist_id' => new Zend_Db_Expr("IF(`root`.`wishlist_id` IS NULL, 0, `root`.`wishlist_id`)"), 
                'visitor_id'  => new Zend_Db_Expr("IF(`root`.`visitor_id` IS NULL, 0, `root`.`visitor_id`)")
            ))
            ->group($groupby);
        }
                
        $couponTable = $this->getResource()->getTable('bronto_reminder/coupon');
        
        $select->joinLeft(
            array('c' => $couponTable),
            'c.customer_id=root.customer_id AND c.store_id=root.store_id AND c.rule_id=:rule_id',
            array('c.coupon_id')
        );
        
        return $select;
    }

    /**
     * Get SQL select.
     * Rewrited for cover root conditions combination with additional condition by customer
     *
     * @param null|Bronto_Reminder_Model_Rule        $rule
     * @param int                          | Zend_Db_Expr $website
     * @return Varien_Db_Select
     */
    public function getConditionsSql($rule, $website)
    {
        $select     = $this->_prepareConditionsSql($rule, $website);
        $required   = $this->_getRequiredValidation();
        $aggregator = ($this->getAggregator() == 'all') ? ' AND ' : ' OR ';
        $operator   = $required ? '=' : '<>';
        $conditions = array();
        
        foreach ($this->getConditions() as $condition) {
            if ($sql = $condition->getConditionsSql($rule, $website)) {
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
