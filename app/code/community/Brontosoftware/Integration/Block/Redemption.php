<?php

class Brontosoftware_Integration_Block_Redemption extends Mage_Core_Block_Template
{
    private $_siteId;
    private $_helper;
    private $_order;

    /**
     * @see parent
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::getModel('brontosoftware_integration/settings');
        $this->_siteId = Mage::getModel('brontosoftware_connector/settings')->getSiteId();
        $this->setTemplate('brontosoftware/integration/redemption.phtml');
    }

    /**
     * Helper to determine if the snippet should show at all
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (
            $this->_siteId &&
            $this->getOrder() &&
            $this->getOrder()->getCouponCode() &&
            $this->_helper->isCouponEnabled()
        );
    }

    /**
     * Gets the last order in the session
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            if ($orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->getId()) {
                    $this->_order = $order;
                }
            }
        }
        return $this->_order;
    }

    /**
     * Gets the sitehash associated with Coupon Manager
     *
     * @return string
     */
    public function getSiteId()
    {
        return $this->_siteId;
    }
}
