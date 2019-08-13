<?php

class Brontosoftware_Cart_Model_Observer extends Brontosoftware_Magento_Cart_ExtensionAbstract
{
    CONST CART_FIDDLE = 'brontosoftware_cart_fiddle';

    /**
     * Override for DI
     */
    public function __construct() {
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/impl_core_quoteManagement'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_queue'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            Mage::getSingleton('brontosoftware_cart/settings'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_platform'),
            new Brontosoftware_Magento_Cart_Event_Source(
                Mage::getSingleton('brontosoftware_cart/settings'),
                Mage::getSingleton('brontosoftware_connector/settings'),
                Mage::getSingleton('brontosoftware_order/settings'),
                Mage::getSingleton('brontosoftware_connector/impl_core_cookies')));
    }

    /**
     * Handle the generic site fiddle
     *
     * @param mixed $observer
     * @return void
     */
    public function handleSiteFiddle($observer)
    {
        $checkout = Mage::getSingleton('checkout/session');
        if ($checkout->getQuoteId() && $checkout->getQuote()->getIsActive()) {
            Mage::dispatchEvent(self::CART_FIDDLE, array(
                'quote' => $checkout->getQuote()->setUpdatedAt(date('c'))
            ));
        }
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_cart')->__($message);
    }
}
