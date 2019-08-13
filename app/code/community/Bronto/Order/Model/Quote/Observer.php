<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.7
 */
class Bronto_Order_Model_Quote_Observer
{
    /**
     * This event should only fire on the "frontend". It reads Bronto's
     * "tid" cookie value and assigns to the shopping cart.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addTidToQuote(Varien_Event_Observer $observer)
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getQuote();
        
        /* @var $contactQueue Bronto_Order_Model_Queue */
        $orderRow = Mage::getModel('bronto_order/queue')
                ->getOrderRow(null, $quote->getId(), $quote->getStoreId());
        
        foreach (Mage::getModel('core/cookie')->get() as $key => $value) {
            if (stripos($key, "tid_") !== false) {
                $orderRow->setBrontoTid($value)->save();
                
                break;
            }
        }
    }
}
