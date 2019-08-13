<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_LoadController extends Mage_Core_Controller_Front_Action
{
    /**
     * Open quote by link
     * Example: /reminder/load/index/id/1
     */
    public function indexAction()
    {
        $storeCode = $this->getRequest()->getParam('___store', 'default');
        $store     = $this->_getStoreByCode($storeCode);
        $storeId   = $store->getId();
        Mage::app()->setCurrentStore($storeId);
        $quote      = false;
        $quoteId    = $this->getRequest()->getParam('id');
        $wishlist   = false;
        $wishlistId = $this->getRequest()->getParam('wishlist_id');
        $ruleId     = $this->getRequest()->getParam('rule_id', 0);
        $messageId  = $this->getRequest()->getParam('message_id', 0);

        if (!empty($quoteId)) {
            $quoteId = Mage::helper('core')->decrypt(base64_decode(urldecode($quoteId)));
            
            if (!empty($quoteId)) {
                /* @var $quote Mage_Sales_Model_Quote */
                $quote = Mage::getModel('sales/quote')
                    ->setStoreId($storeId)
                    ->load($quoteId);

                if ($quote->getId() && $quote->getIsActive()){
                    Mage::getSingleton('checkout/session')->setQuoteId($quote->getId());
                    Mage::getSingleton('checkout/session')->resetCheckout();
                }
                
                $redirectUrl = Mage::app()->getStore()->getUrl('checkout/cart');
            }
        }
        
        if (!empty($wishlistId)) {
            $wishlistId = Mage::helper('core')->decrypt(base64_decode(urldecode($wishlistId)));

            if (!empty($wishlistId)) {
                /* @var $quote Mage_Wishlist_Model_Wishlist */
                $wishlist = Mage::getModel('wishlist/wishlist')
                    ->setStoreId($storeId)
                    ->load($wishlistId);
                
                $redirectUrl = Mage::app()->getStore()->getUrl('wishlist');
            }
        }
        
        if ($ruleId && $quote) {
            $customerId = $quote->getCustomerId();
            $customerId = ($customerId) ? $customerId : 0;
        } elseif ($ruleId && $wishlist) {
            $customerId = $wishlist->getCustomerId();
            $customerId = ($customerId) ? $customerId : 0;
        }
        
        if ($customerId) {
            $log = Mage::getModel('bronto_reminder/rule')
                ->getRuleLogItems($ruleId, $storeId, $customerId, $messageId);

            if (!empty($messageId)) {
                Mage::getSingleton('checkout/session')->setBrontoMessageId($messageId);
            }

            if (isset($log['bronto_delivery_id']) && !empty($log['bronto_delivery_id'])) {
                Mage::getSingleton('checkout/session')->setBrontoDeliveryId($log['bronto_delivery_id']);
            }
        }

        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])){
            $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
        }
        
        $this->_redirectUrl($redirectUrl);
    }

    protected function _getStoreByCode($storeCode)
    {
        $stores = array_keys(Mage::app()->getStores());
        foreach($stores as $id){
            $store = Mage::app()->getStore($id);
            if($store->getCode()==$storeCode) {
                return $store;
            }
        }
        return false;
    }
}
