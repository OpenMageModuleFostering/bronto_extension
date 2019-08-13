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

    /**
     * @param Varien_Event_Observer $observer
     */
    public function updateBrontoFromNewsletterStatus(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bronto_newsletter')->isEnabled()) {
            return;
        }

        try {
            // Insert contact email into queuing table. Cron will
            // then issue an update to Bronto on its next run.
            if ($subscriber = $observer->getEvent()->getSubscriber()) {
                $email = $subscriber->getEmail();
                if (!empty($email)) {
                    /* @var $contactHelper Bronto_Newsletter_Helper_Contact */
                    $contactHelper = Mage::helper('bronto_newsletter/contact');

                    if (!$contactHelper->getUpdateStatus()) {
                        $status = Bronto_Api_Contact::STATUS_TRANSACTIONAL;
                    } else {
                        $status = $contactHelper->getContactByEmail($email)->status;
                    }
                    $this->_saveToQueue($email, $contactHelper, Mage::app()->getStore()->getId());
                }
            }
        } catch (Exception $e) {
            Mage::helper('bronto_newsletter')->writeError($e);
        }
    }

    private function _saveToQueue($email, $helper, $storeId) {
        /* var $contactQueue Bronto_Newsletter_Model_Queue */
        $contactQueue = Mage::getModel('bronto_newsletter/queue');

        if(!$helper->getUpdateStatus()) {
            $status = Bronto_Api_Contact::STATUS_TRANSACTIONAL;
        } else {
            $status = Bronto_Api_Contact::STATUS_ONBOARDING;
        }

        $contactQueue->setSubscriberEmail( $email )
            ->setStatus( $status )
            ->setMessagePreference( 'html' )
            ->setSource( 'api' )
            ->setStore( $storeId )
            ->save();
    }

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
                $contactHelper->saveContact($contact);
                $subscriber->setImported(1)->save();
            } catch(Exception $e) {
                Mage::helper('bronto_newsletter')->writeError($e);
            }
        }
    }
}
