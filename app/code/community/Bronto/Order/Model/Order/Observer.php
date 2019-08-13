<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.5
 */
class Bronto_Order_Model_Order_Observer
{
    /**
     * Called when refunding an order.
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetCreditMemoOrder(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getCreditmemo()->getOrder();
        $order->setBrontoImported(null);
    }

    /**
     * Called when cancelling an order.
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetPaymentCancelOrder(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getPayment()->getOrder();
        $order->setBrontoImported(null);
    }

    /**
     * If an Order's status is changing,
     * just reset the flag anyways...
     *
     * @param Varien_Event_Observer $observer
     */
    public function markOrderForReimport(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();
        $order->setBrontoImported(null);
    }
}
