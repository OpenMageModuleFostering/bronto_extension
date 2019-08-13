<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.5
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

        foreach (Mage::getModel('core/cookie')->get() as $key => $value) {
            if (stripos($key, "tid_") !== false) {
                $quote->setBrontoTid($value);
                break;
            }
        }
    }
}
