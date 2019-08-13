<?php

/**
 * @package   Bronto\Newsletter
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.3.5
 */
class Bronto_Newsletter_Model_Observer
    extends Mage_Core_Model_Abstract
{

    const NOTICE_IDENTIFER = 'bronto_newsletter';
    const BOX_UNCHECKED    = 0;
    const BOX_CHECKED      = 1;
    const BOX_NOT_CHANGED  = 2;

    private $_helper;

    public function __construct()
    {
        /* @var $_helper Bronto_Newsletter_Helper_Data */
        $this->_helper = Mage::helper(self::NOTICE_IDENTIFER);
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return mixed
     */
    public function checkBrontoRequirements(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return;
        }

        // Verify Requirements
        if (!$this->_helper->varifyRequirements(self::NOTICE_IDENTIFER, array('soap', 'openssl'))) {
            return;
        }
    }

    /**
     * Observes module becoming enabled and displays message warning user to configure settings
     * @param Varien_Event_Observer $observer
     */
    public function watchEnableAction(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('bronto_newsletter')->__(Mage::helper('bronto_newsletter')->getModuleEnabledText()));
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
        if (!$this->_helper->isEnabled()) {
            return;
        }

        $controllerAction = $observer->getControllerAction();
        if ($controllerAction instanceof Mage_Checkout_OnepageController) {
            Mage::getSingleton('checkout/session')->unsIsSubscribed();
            $params = Mage::app()->getRequest()->getParams();

            if (
                isset($params['billing']['is_subscribed']) &&
                ($params['billing']['is_subscribed'] === '1' ||
                    $params['billing']['is_subscribed'] === '0')
            ) {
                $isSubscribed = (int) $params['billing']['is_subscribed'];
                Mage::getSingleton('checkout/session')->setIsSubscribed($isSubscribed);
            }
            else {
                Mage::getSingleton('checkout/session')->setIsSubscribed(self::BOX_NOT_CHANGED);
            }
        }

        return $observer;
    }

    /**
     * Get Bronto Contact Row via Email address
     *
     * @param string $email
     *
     * @return boolean|Bronto_Api_Contact_Row
     */
    protected function _getBrontoContact($email)
    {
        try {
            /* @var $contact Bronto_Api_Contact_Row */
            $contact = Mage::helper('bronto_newsletter/contact')->getContactByEmail(
                $email,
                NULL,
                Mage::app()->getStore()->getId()
            );

            return $contact;
        }
        catch (Exception $e) {
            $this->_helper->writeError($e);

            return FALSE;
        }
    }

    /**
     * Observe checkout event and handle assigning status
     *
     * @param Varien_Event_Observer $observer
     *
     * @return boolean|Varien_Event_Observer
     */
    public function handleSubscriptionAtCheckout(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return;
        }

        // Get Subscription status from session
        $isSubscribed = Mage::getSingleton('checkout/session')->getIsSubscribed();

        // If Subscription status isn't set, we do nothing
        if (!is_int($isSubscribed)) {
            return $observer;
        }

        try {
            // Get e-mail address we are working with
            $email = $observer->getEvent()->getOrder()->getData('customer_email');

            if (empty($email)) {
                $this->_helper->writeError('No customer_email was provided.');

                return FALSE;
            }

            /* @var $subscriber Mage_Newsletter_Model_Subscriber */
            if (!$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email)) {
                $this->_helper->writeError('Unable to create subscriber object');

                return FALSE;
            }

            /* @var $contact Bronto_Api_Contact_Row */
            if (!$contact = $this->_getBrontoContact($email)) {
                $this->_helper->writeError('Unable to create contact object');

                return FALSE;
            }

            // Determine action
            switch ($isSubscribed) {
                case self::BOX_CHECKED:
                    // Subscribe the Customer
                    if (!$subscriber->isSubscribed()) {
                        return $subscriber->subscribe($email);
                    }
                    break;
                case self::BOX_UNCHECKED:
                    // Unsubscribe the Customer if subscribed, Make Transactional if not in bronto
                    if ($subscriber->isSubscribed()) {
                        return $subscriber->unsubscribe();
                    }
                    elseif (!$contact->id && !$subscriber->isSubscribed()) {
                        $this->_makeTransactional($subscriber, $email);
                    }
                    break;
                case self::BOX_NOT_CHANGED:
                    // Make Customer Transactional if not in bronto
                    if (!$contact->id && !$subscriber->isSubscribed()) {
                        $this->_makeTransactional($subscriber, $email);
                    }
                    break;
                default:
                    // Intentionally blank
                    break;
            }
        }
        catch (Exception $e) {
            $this->_helper->writeError($e);
        }

        return $observer;
    }

    /**
     * Handle setting subscriber as transactional in bronto queue and
     * removing from magento subscription
     *
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @param string                           $email
     *
     * @return boolean|Mage_Newsletter_Model_Subscriber
     */
    private function _makeTransactional(Mage_Newsletter_Model_Subscriber $subscriber, $email)
    {
        /* @var $contact Bronto_Api_Contact_Row */
        if (!$contact = $this->_getBrontoContact($email)) {
            $this->_helper->writeError('Unable to create contact object');

            return FALSE;
        }

        // Get Customer using the email provided
        $ownerId = Mage::getModel('customer/customer')
                   ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                   ->loadByEmail($email)
                   ->getId();

        if (!$ownerId) {
            $ownerId = Mage::getSingleton('customer/session')->getId();
        }

        // Set Magento Subscriber and Status
        $subscriber->setCustomerId($ownerId);
        $subscriber->setSubscriberEmail($email);
        $subscriber->setStoreId(Mage::app()->getStore()->getId());
        if ($contact->status == Bronto_Api_Contact::STATUS_UNSUBSCRIBED) {
            $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
        }
        else {
            $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
        }

        $subscriber->save();

        return $subscriber;
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function updateBrontoFromNewsletterStatus(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return;
        }

        // Insert contact email into queuing table. Cron will
        // then issue an update to Bronto on its next run.
        try {
            /* @var $subscriber Mage_Newsletter_Model_Subscriber */
            if (!$subscriber = $observer->getEvent()->getSubscriber()) {
                $this->_helper->writeError('Unable to create subscriber object');

                return FALSE;
            }

            // Send to queue
            $this->_saveToQueue($subscriber, Mage::app()->getStore()->getId());
        }
        catch (Exception $e) {
            $this->_helper->writeError($e);
        }
    }

    /**
     * Add Subscriber to Bronto Newsletter Opt-in queue
     *
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @param int                              $storeId
     *
     * @return void
     */
    private function _saveToQueue($subscriber, $storeId)
    {
        // Get e-mail address we are working with
        $email = $subscriber->getEmail();
        if (empty($email)) {
            $this->_helper->writeError('Subscriber does not have an email address.');

            return FALSE;
        }

        // Get Calculated Status
        $status = Mage::helper('bronto_newsletter/contact')->getQueueStatus($subscriber);

        /* @var $contactQueue Bronto_Newsletter_Model_Queue */
        $contactQueue = Mage::getModel('bronto_newsletter/queue')
                        ->getContactRow($subscriber->getId(), $storeId);

        // If ContactQueue status doesn't match subscriber status, replace it
        if ($status != $contactQueue->getStatus()) {
            $contactQueue->setSubscriberEmail($subscriber->getEmail())
            ->setStatus($status)
            ->setMessagePreference('html')
            ->setSource('api')
            ->setImported(0)
            ->save();
        }
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function processSubscribersForStore($storeId, $limit)
    {
        // Define default results
        $result = array('total' => 0, 'success' => 0, 'error' => 0);

        // If limit is false or 0, return
        if (!$limit) {
            $this->_helper->writeDebug('  Limit empty. Skipping...');

            return $result;
        }

        if (is_object($storeId)) {
            $store   = $storeId;
            $storeId = $store->getId();
        }
        else {
            $store   = Mage::app()->getStore($storeId);
            $storeId = $store->getId();
        }

        $this->_helper->writeDebug("Starting Subscriber Opt-In process for store: {$store->getName()} ({$storeId})");

        if (!$store->getConfig(Bronto_Newsletter_Helper_Data::XML_PATH_ENABLED)) {
            $this->_helper->writeDebug('  Module disabled for this store. Skipping...');

            return FALSE;
        }

        $helper = Mage::helper('bronto_newsletter/contact');

        $lists = $helper->getListIds($storeId);

        // Get Subscriber Queue for store
        /* var $subscribers Bronto_Newsletter_Model_Mysql4_Queue_Collection */
        $subscribers = Mage::getModel('bronto_newsletter/queue')
                       ->getCollection()
                       ->addBrontoNotImportedFilter()
                       ->addBrontoNotSuppressedFilter()
                       ->addStoreFilter($storeId)
                       ->setPageSize($limit)
                       ->getItems();

        foreach ($subscribers as $subscriber) {
            try {
                /* @var $contact Bronto_Api_Contact_Row */
                $contact = $helper->getContactByEmail($subscriber->getSubscriberEmail(), NULL, $storeId);

                // Get List Details
                foreach ($lists as $listId) {
                    if ($list = $helper->getListData($listId)) {
                        $listName = $list->label;
                    }
                    else {
                        Mage::throwException(
                            "The list ({$listId}) was not found.  This may indicate that it does not exist.  Try re-saving the config"
                        );
                    }
                    $helper->writeInfo("  Adding Contact to list: {$listName}");
                    $contact->addToList($listId);
                }

                // Save List Update at least
                $contact->save();

                // If Bronto Status is 'Bounced', mark suppressed, show error and continue foreach
                if ($contact->status == Bronto_Api_Contact::STATUS_BOUNCE) {
                    $bounceMessage = "Subscriber {$contact->email} Has Been Bounced in Bronto";
                    $subscriber->setBrontoSuppressed($bounceMessage)->save();
                    Mage::throwException($bounceMessage);
                }

                if ($helper->getUpdateStatus()) {
                    switch ($subscriber->getStatus()) {
                        case Bronto_Api_Contact::STATUS_UNCONFIRMED:
                        case Bronto_Api_Contact::STATUS_TRANSACTIONAL:
                            if ($contact->id && $contact->status != Bronto_Api_Contact::STATUS_UNSUBSCRIBED) {
                                $helper->writeInfo(
                                    "  Keeping Contact ({$contact->email}) status as: {$contact->status}"
                                );
                                break;
                            }
                            $contact->status = $subscriber->getStatus();
                            $helper->writeInfo("  Setting Contact ({$contact->email}) status to: {$contact->status}");
                            break;

                        case Bronto_Api_Contact::STATUS_ACTIVE:
                            if ($contact->status == Bronto_Api_Contact::STATUS_UNSUBSCRIBED &&
                                $subscriber->getImported() == 2
                            ) {
                                $helper->writeInfo(
                                    "  Keeping Contact ({$contact->email}) status as: {$contact->status}"
                                );
                                break;
                            }
                            $contact->status = $subscriber->getStatus();
                            $helper->writeInfo("  Setting Contact ({$contact->email}) status to: {$contact->status}");
                            break;

                        default:
                            $contact->status = $subscriber->getStatus();
                            $helper->writeInfo("  Setting Contact ({$contact->email}) status to: {$contact->status}");
                            break;
                    }
                }

                $contact->save();

                $subscriber->setImported(1)->save();

                $result['success']++;
            }
            catch (Exception $e) {
                // 315 means contact on suppression list, so suppress
                if (315 == $e->getCode()) {
                    $subscriber->setBrontoSuppressed($e->getMessage());
                }

                $this->_helper->writeError($e);

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

        $limit = $this->_helper->getLimit();

        $stores = Mage::app()->getStores(TRUE);
        foreach ($stores as $_store) {
            if ($limit <= 0) {
                continue;
            }
            $storeResult = $this->processSubscribersForStore($_store, $limit);
            $result['total']   += $storeResult['total'];
            $result['success'] += $storeResult['success'];
            $result['error']   += $storeResult['error'];
            $limit = $limit - $storeResult['total'];
        }

        return $result;
    }
}
