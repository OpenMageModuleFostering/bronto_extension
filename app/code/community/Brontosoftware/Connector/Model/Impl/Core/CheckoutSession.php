<?php

class Brontosoftware_Connector_Model_Impl_Core_CheckoutSession implements Brontosoftware_Magento_Core_Sales_CheckoutSessionInterface
{
    /**
     * @see parent
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @see parent
     */
    public function getQuoteId()
    {
        return Mage::getSingleton('checkout/session')->getQuoteId();
    }

    /**
     * @see parent
     */
    public function resetCheckout()
    {
        Mage::getSingleton('checkout/session')->resetCheckout();
        return $this;
    }

    /**
     * @see parent
     */
    public function getInitializedCart()
    {
        $cart = Mage::getSingleton('checkout/cart');
        $cart->init();
        return $cart;
    }
}
