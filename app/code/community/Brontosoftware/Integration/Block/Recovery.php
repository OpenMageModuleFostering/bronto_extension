<?php

class Brontosoftware_Integration_Block_Recovery extends Mage_Core_Block_Template
{
    protected $_helper;
    protected $_settings;
    protected $_quote;
    protected $_order;
    protected $_currency;

    /**
     * @see parent
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::getModel('brontosoftware_order/settings');
        $this->setTemplate('brontosoftware/integration/recovery.phtml');
        $this->_settings = Mage::getModel('brontosoftware_integration/settings');
    }

    /**
     * Forwards helper call
     *
     * @return string
     */
    public function getCartRecoveryEmbedCode()
    {
        return $this->_settings->getCartRecoveryEmbedCode();
    }

    /**
     * Gets the checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Gets the current cart if available
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (is_null($this->_quote)) {
            $this->_quote = false;
            $quote = $this->getCheckout()->getQuote();
            if ($quote->getId()) {
                $this->_quote = $quote;
            }
        }
        return $this->_quote;
    }

    /**
     * Gets the last order placed, if available
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = false;
            $orderId = $this->getCheckout()->getLastOrderId();
            $capturedOrderId = $this->getCheckout()->getCapturedOrderId();
            if (!$this->getQuote() && $orderId && $orderId != $capturedOrderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->getId()) {
                    $this->_order = $order;
                    $this->getCheckout()->setCapturedOrderId($orderId);
                }
            }
        }
        return $this->_order;
    }

    /**
     * Determines if it should write to the DOM
     *
     * @return bool
     */
    public function shouldWriteDom()
    {
      return $this->_settings->isShadowDom('store', Mage::app()->getStore())
          && $this->getSalesObject();
    }

    /**
     * Gets the order or quote
     *
     * @return mixed
     */
    public function getSalesObject()
    {
        if ($this->getOrder()) {
            return $this->getOrder();
        } else if ($this->getQuote()) {
            return $this->getQuote();
        } else {
            return false;
        }
    }

    /**
     * Gets the checkout URL
     * @return string
     */
    public function getCheckoutUrl()
    {
        $quote = $this->getQuote();
        if ($quote) {
            return $this->_settings->getRedirectUrl($quote->getId(), Mage::app()->getStore());
        } else {
            return Mage::app()->getStore()->getUrl('checkout/cart');
        }
    }

    /**
     * Forwards helper call
     *
     * @param mixed $lineItem
     * @return string
     */
    public function renderCategories($lineItem)
    {
        return $this->_helper->getItemCategories($lineItem);
    }

    /**
     * Forwards helper call
     *
     * @param mixed $lineItem
     * @return string
     */
    public function getDescription($lineItem)
    {
        return $this->_helper->getItemDescription($lineItem);
    }

    /**
     * Forwards helper call
     *
     * @param mixed $lineItem
     * @return string
     */
    public function getProductUrl($lineItem)
    {
        return $this->_helper->getItemUrl($lineItem);
    }

    /**
     * Forwards helper call
     *
     * @param mixed $lineItem
     * @return string
     */
    public function getOther($lineItem)
    {
        return $this->_helper->getItemOtherField($lineItem);
    }

    /**
     * Forwards helper call
     *
     * @param mixed $lineItem
     * @return string
     */
    public function getImage($lineItem)
    {
        return $this->_helper->getItemImage($lineItem);
    }

    /**
     * Gets quantity in basket or order
     *
     * @return int
     */
    public function getQty($lineItem)
    {
        if ($lineItem instanceof Mage_Sales_Model_Order_Item) {
            return $lineItem->getQtyOrdered();
        } else {
            return $lineItem->getQty();
        }
    }

    /**
     * Gets discount amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        $object = $this->getSalesObject();
        if ($object instanceof Mage_Sales_Model_Quote) {
            return $object->getSubtotal() - $object->getSubtotalWithDiscount();
        } else {
            return $object->getDiscountAmount();
        }
    }

    /**
     * Gets the display for the sales object
     *
     * @param mixed $lineItem
     * @return float
     */
    public function getPrice($lineItem)
    {
        return $this->_helper->getItemPrice($lineItem, true);
    }

    /**
     * Gets the display for the original price
     *
     * @param mixed $lineItem
     * @return float
     */
    public function getOriginalPrice($lineItem)
    {
        return $this->getPrice($lineItem);
    }

    /**
     * Gets the display for the row total
     *
     * @param mixed $lineItem
     * @return float
     */
    public function getRowTotal($lineItem)
    {
        return $this->_helper->getItemRowTotal($lineItem, true);
    }

    /**
     * Gets the currency code on the quote or order
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        if ($this->getOrder()) {
            return $this->getOrder()->getOrderCurrencyCode();
        } else {
            return $this->getQuote()->getQuoteCurrencyCode();
        }
    }

    /**
     * Forwards call to helper
     *
     * @return array
     */
    public function getFlatItems()
    {
        return $this->_helper->getFlatItems($this->getSalesObject());
    }

    /**
     * Forwards call to helper
     *
     * @return string
     */
    public function getItemName($lineItem)
    {
        return $this->_helper->getItemName($lineItem);
    }

    /**
     * Formats any given price with a 2 rounding precision
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        if (is_null($this->_currency)) {
            $this->_currency = Mage::getModel('directory/currency')
                ->load($this->getCurrencyCode());
        }
        return $this->_currency->formatTxt($price, array(
            'precision' => 2,
            'display' => Zend_Currency::NO_SYMBOL
        ));
    }
}
