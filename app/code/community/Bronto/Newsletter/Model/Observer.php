<?php

/**
 * @package   Newsletter
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.3.5
 */
class Bronto_Newsletter_Model_Observer extends Mage_Core_Model_Abstract
{
    const NOTICE_IDENTIFER = 'bronto_newsletter';
    const BOX_UNCHECKED    = 0;
    const BOX_CHECKED      = 1;
    const BOX_NOT_SHOWN    = 2;

    /**
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function checkBrontoRequirements(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return;
        }

        // Verify Requirements
        if (!Mage::helper(self::NOTICE_IDENTIFER)->varifyRequirements(self::NOTICE_IDENTIFER, array('soap', 'openssl'))) {
            return;
        }
    }

    /**
     * This event fires when customer continues past the Billing Info step
     * on the onepage checkout. We set a flag here in the session to avoid
     * actually doing anything until checkout is complete.
     *
     * @param Varien_Event_Observer $observer
     */
    public function setSubscriptionAtBillingStep(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bronto_newsletter')->isEnabled()) {
            return;
        }

        $params = Mage::app()->getRequest()->getParams();
        if (isset($params['billing']['is_subscribed'])) {
            $isSubscribed = (int) $params['billing']['is_subscribed'];
            Mage::getSingleton('checkout/session')->setIsSubscribed($isSubscribed);
        } else {
            Mage::getSingleton('checkout/session')->setIsSubscribed(self::BOX_UNCHECKED);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function handleSubscriptionAtCheckout(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bronto_newsletter')->isEnabled()) {
            return;
        }

        try {
            // Get e-mail address we are working with
            $email = $observer->getEvent()->getOrder()->getData('customer_email');
            if (empty($email)) {
                Mage::helper('bronto_newsletter')->writeError('No customer_email was provided.');
                return false;
            }

            /* @var $subscriber Mage_Newsletter_Model_Subscriber */
            $subscriber   = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            $isSubscribed = Mage::getSingleton('checkout/session')->getIsSubscribed();
            
            switch ($isSubscribed) {
                case self::BOX_UNCHECKED:
                    // Unsubscribe the Customer
                    if ($subscriber && $subscriber->isSubscribed()) {
                        return $subscriber->unsubscribe();
                    } else {
                        // Make Custmoer Transactional
                        $this->_makeTransactional($subscriber, $email);
                    }
                    break;
                case self::BOX_CHECKED:
                    // Subscribe the Customer
                    if (!$subscriber || !$subscriber->isSubscribed()) {
                        return $subscriber->subscribe($email);
                    }
                    break;
                case self::BOX_NOT_SHOWN:
                    // Just save the Customer
                    $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
                    $subscriber->save();
                    break;
            }

        } catch (Exception $e) {
            Mage::helper('bronto_newsletter')->writeError($e);
        }
    }
    
    private function _makeTransactional($subscriber, $email)
    {
        // Check For Existing Contact from Bronto
        try {
            $contactHelper = Mage::helper('bronto_common/contact');
            $contact = $contactHelper->getContactByEmail($subscriber->getSubscriberEmail(), null, Mage::app()->getStore()->getId());
        } catch (Exception $e) {
            Mage::helper('bronto_newsletter')->writeError($e);
            return false;
        }
        
        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        
        if (!$ownerId) {
            $ownerId = Mage::getSingleton('customer/session')->getId();
        }
        
        $subscriber->setCustomerId($ownerId); 
        $subscriber->setSubscriberEmail($email);
        $subscriber->setStoreId(Mage::app()->getStore()->getId());
        if ($contact->status == Bronto_Api_Contact::STATUS_UNSUBSCRIBED) {
            $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
        } else {
            $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
        }
        
        $subscriber->save();
        
        $this->_saveToQueue($subscriber, Mage::app()->getStore()->getId());
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function updateBrontoFromNewsletterStatus(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bronto_newsletter')->isEnabled()) {
            return;
        }

        // Insert contact email into queuing table. Cron will
        // then issue an update to Bronto on its next run.        
        try {
            /* @var $subscriber Mage_Newsletter_Model_Subscriber */
            if ($subscriber = $observer->getEvent()->getSubscriber()) {
                $email = $subscriber->getEmail();  
                if (!empty($email)) {
                    $this->_saveToQueue($subscriber, Mage::app()->getStore()->getId());
                }
            }
        } catch (Exception $e) {
            Mage::helper('bronto_newsletter')->writeError($e);
        }
    }

    /**
     * Add Subscriber to Bronto Newsletter Opt-in queue
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @param int $storeId
     * @param string $status
     * @return void
     */
    private function _saveToQueue($subscriber, $storeId) 
    {
        // Get Calculated Status
        $status = $this->_getQueueStatus($subscriber);
        
        /* @var $contactQueue Bronto_Newsletter_Model_Queue */
        $contactQueue = Mage::getModel('bronto_newsletter/queue');

        $contact = $contactQueue->getContactRow($subscriber->getId(), $storeId)
            ->setSubscriberEmail($subscriber->getEmail())
            ->setStatus($status)
            ->setMessagePreference('html')
            ->setSource('api')
            ->setImported(0)
            ->save();
    }
    
    /**
     * Determine Status to use when sending subscriber to opt-in queue
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @param int $storeId
     * @return boolean
     */
    private function _getQueueStatus($subscriber)
    {
        // Set correct status based on subscriber status
        switch ($subscriber->getStatus()) {
            case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                $status = Bronto_Api_Contact::STATUS_ONBOARDING;
                break;

            case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                $status = Bronto_Api_Contact::STATUS_UNSUBSCRIBED;
                break;

            case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
                $status = Bronto_Api_Contact::STATUS_TRANSACTIONAL;
                break;
                
            default:
                $status = false;
                break;
    }

        return $status;
    }
    
    /**
     * @param int $storeId
     * @return array
     */
    public function processSubscribersForStore($storeId = null)
    {
        if (is_object($storeId)) {
            $store   = $storeId;
            $storeId = $store->getId();
        } else {
            $store   = Mage::app()->getStore($storeId);
            $storeId = $store->getId();
        }
        
        $result = array('total' => 0, 'success' => 0, 'error' => 0);
        Mage::helper('bronto_newsletter')->writeDebug("Starting Subscriber Opt-In process for store: {$store->getName()} ({$storeId})");

        if (!$store->getConfig(Bronto_Newsletter_Helper_Data::XML_PATH_ENABLED)) {
            Mage::helper('bronto_newsletter')->writeDebug('  Module disabled for this store. Skipping...');
            return false;
        }

        $limit = $store->getConfig(Bronto_Newsletter_Helper_Data::XML_PATH_LIMIT);
        if (!$limit) {
            Mage::helper('bronto_newsletter')->writeDebug('  Limit empty. Skipping...');
            return false;
        }
        
        $helper        = Mage::helper('bronto_newsletter/contact');
        $contactHelper = Mage::helper('bronto_common/contact');
        
        $lists = $helper->getListIds($storeId);
        
        // Get Subscriber Queue for store
        /* var $subscribers Bronto_Newsletter_Model_Mysql4_Queue_Collection */
        $subscribers = Mage::getModel('bronto_newsletter/queue')
            ->getCollection()
            ->addFilter('imported', 0)
            ->addFilter('store', $storeId);
        
        $subscribers->getSelect()->limit($limit);
        foreach ($subscribers as $subscriber) {
            try {
                $contact = $contactHelper->getContactByEmail($subscriber->getSubscriberEmail(), null, $storeId);
                
                foreach ($lists as $listId) {
                    $contactHelper->writeInfo("  Adding Contact to list: {$listId}");
                    $contact->addToList($listId);
                }
                
                if ($helper->getUpdateStatus()) {
                    $contact->status = $subscriber->getStatus();
                    $contactHelper->writeInfo("  Setting Contact ({$contact->email}) status to: {$contact->status}");
                }
                $contact->save();
                $subscriber->setImported(1)->save();
                $result['success']++;
            } catch (Exception $e) {
                Mage::helper('bronto_newsletter')->writeError($e);
                
                $subscriber->setImported(0)->save();
                $result['error']++;
            }
            $result['total']++;
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function processSubscribers()
    {
        $result = array(
            'total'   => 0,
            'success' => 0,
            'error'   => 0,
        );

        $stores = Mage::app()->getStores();
        foreach ($stores as $_store) {
            $storeResult = $this->processSubscribersForStore($_store);
            $result['total']   += $storeResult['total'];
            $result['success'] += $storeResult['success'];
            $result['error']   += $storeResult['error'];
        }

        return $result;
    }

    /**
     * @depricated
     */
    static public function cronImport() {
        /* var $contactHelper Bronto_Newsletter_Helper_Contact */
        $contactHelper = Mage::helper('bronto_newsletter/contact');
        /* var $subscribers Bronto_Newsletter_Model_Mysql4_Queue_Collection */
        $subscribers = Mage::getModel('bronto_newsletter/queue')
            ->getCollection()
            ->addFilter('imported', 0);

        foreach($subscribers as $subscriber) {
            $email = $subscriber->getSubscriberEmail();
            $contact = $contactHelper->getContactByEmail($email, null, $subscriber->getStore());
            $contact->status = $subscriber->getStatus();

            try {
                //$contactHelper->saveContact($contact);
                $subscriber->setImported(1)->save();
            } catch(Exception $e) {
                Mage::helper('bronto_newsletter')->writeError($e);
            }
        }
    }
}
