<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 * @method      Bronto_Reminder_Model_Mysql4_Rule _getResource()
 */
class Bronto_Reminder_Model_Rule extends Mage_Rule_Model_Rule
{
    /**
     * Contains data defined per store view, will be used in Messages as variables
     * @var array
     */
    protected $_messageData = array();

    protected function _construct()
    {
        parent::_construct();
        $this->_init('bronto_reminder/rule');
    }

    /**
     * Perform actions after object load
     *
     * @return Bronto_Reminder_Model_Rule
     */
    protected function _afterLoad()
    {
        Mage_Core_Model_Abstract::_afterLoad();

        $version = Mage::getVersionInfo();
        if (   1 == $version['major']
            && (6 >= $version['minor'] || 9 == $version['minor'])
        ) {
            $conditionsArr = unserialize($this->getConditionsSerialized());
            if (!empty($conditionsArr) && is_array($conditionsArr)) {
                    $this->getConditions()->loadArray($conditionsArr);
            }
        }

        $messageData = $this->_getResource()->getMessageData($this->getId());

        foreach ($messageData as $data) {
            $message = (empty($data['message_id'])) ? null : $data['message_id'];
            $this->setData('store_message_' . $data['store_id'], $message);
        }

        return $this;
    }

    /**
     * Perform actions before object save.
     */
    protected function _beforeSave()
    {
        $this->setConditionSql(
            $this->getConditions()->getConditionsSql(null, new Zend_Db_Expr(':website_id'))
        );

        if (!$this->getSalesruleId()) {
            $this->setSalesruleId(null);
        }
        parent::_beforeSave();
    }

    /**
     * Live website ids data as is
     *
     * @return Bronto_Reminder_Model_Rule
     */
    protected function _prepareWebsiteIds()
    {
        return $this;
    }

    /**
     * Return conditions instance
     *
     * @return Bronto_Reminder_Model_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('bronto_reminder/rule_condition_combine_root');
    }

    /**
     * Get rule associated website ids
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        if (!$this->hasData('website_ids')) {
            $this->setData('website_ids', $this->_getResource()->getWebsiteIds($this->getId()));
        }
        return $this->_getData('website_ids');
    }

    /**
     * Send reminder emails
     *
     * @param bool $dontSend
     * @return Bronto_Reminder_Model_Rule
     */
    public function sendReminderEmails($dontSend = false)
    {
        /* @var $mail Bronto_Reminder_Model_Email_Message */
        $mail     = Mage::getModel('bronto_reminder/email_message');
        $limit    = Mage::helper('bronto_reminder')->getOneRunLimit();
        $identity = Mage::helper('bronto_reminder')->getEmailIdentity();

        $this->_matchCustomers();

        $recipients = $this->_getResource()->getCustomersForNotification($limit, $this->getRuleId());
        $recipients = array_merge( $recipients, $this->_getGuestAbandons() );

        if ($dontSend) {
            return $this;
        }

        $total   = 0;
        $success = 0;
        $error   = 0;
        foreach ($recipients as $recipient) {
            $total++;

            if($recipient['customer_id'] != 0) {
                /* @var $customer Mage_Customer_Model_Customer */
                $customer = Mage::getModel('customer/customer')->load($recipient['customer_id']);
                if (!$customer || !$customer->getId()) {
                    $error++;
                    continue;
                }
            } else {
                // Guest Abandon.  Create Customer on the fly
                $storeId = $recipient['guest']->getStoreId();
                $customer = Mage::getModel('customer/customer');
                $customer
                    ->setFirstName($recipient['guest']->getFirstName())
                    ->setLastName($recipient['guest']->getLastName())
                    ->setEmail($recipient['guest']->getEmailAddress())
                    ->setStoreId($storeId)
                    ->setId(0)
                    ->setWebsiteId(Mage::getModel('core/store')->load($storeId)->getWebsiteId());
            }

            if ($customer->getStoreId()) {
                $store = $customer->getStore();
            } else {
                $store = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore();
            }

            $messageData = $this->getMessageData($recipient['rule_id'], $store->getId(), $customer->getWebsiteId());
            if (!$messageData) {
                Mage::helper('bronto_reminder')->writeInfo("Rule doesn't have an associated Bronto message.");
                $error++;
                continue;
            }

            $coupon = false;
            if (class_exists('Mage_SalesRule_Model_Coupon', false)) {
                /* @var $coupon Mage_SalesRule_Model_Coupon */
                $coupon = Mage::getModel('salesrule/coupon')->load($recipient['coupon_id']);
            }

            /* @var $quote Mage_Sales_Model_Quote */
            if($customer->getId() != 0) {
                $quote = Mage::getModel('sales/quote')
                    ->setStoreId($store->getId())
                    ->loadByCustomer($customer->getId());
            } else {
                // Load quote stored for guest
                $quote = Mage::getModel('sales/quote')
                    ->setStoreId($store->getId())
                    ->loadByIdWithoutStore($recipient['guest']->getQuoteId());
            }

            $templateVars = array(
                'store'                 => $store,
                'customer'              => $customer,
                'promotion_name'        => $messageData['label'],
                'promotion_description' => $messageData['description'],
                'coupon'                => $coupon,
                'rule'                  => $this,
                'quote'                 => $quote
            );

            Mage::helper('bronto_reminder')->writeDebug('Sending message to: ' . $customer->getEmail());

            try {
                $message = Mage::helper('bronto_reminder/message')->getMessageById($messageData['message_id'], $store->getId(), $customer->getWebsiteId());

                $mail->sendTransactional(
                    $message,
                    $identity,
                    $customer->getEmail(),
                    null,
                    $templateVars,
                    $store->getId()
                );

            } catch (Exception $e) {
                Mage::helper('bronto_reminder')->writeError('  ' . $e->getMessage());
            }

            if ($mail->getSentSuccess()) {
                Mage::helper('bronto_reminder')->writeDebug('  Success');

                if($customer->getId() != 0) {
                    $this->_getResource()->addNotificationLog(
                        $recipient['rule_id'], $customer->getId(), $mail->getLastDeliveryId(), $messageData['message_id']
                    );

                } else {
                    // Add notification log for guest abandon email
                    $this->_getResource()->addNotificationLog(
                        $recipient['rule_id'], 0, $mail->getLastDeliveryId(), $messageData['message_id']
                    );
                    // Update guest to reflect they have received a reminder notification
                    $recipient['guest']->setEmailSent(1)->save();
                }

                $success++;
            } else {
                Mage::helper('bronto_reminder')->writeDebug('  Failed');
                $this->_getResource()->updateFailedEmailsCounter($recipient['rule_id'], $customer->getId());
                $error++;
            }
        }

        return array(
            'total'   => $total,
            'success' => $success,
            'error'   => $error,
        );
    }

    /**
     * Match customers and assign coupons
     *
     * @return Bronto_Reminder_Model_Observer
     */
    protected function _matchCustomers()
    {
        $threshold  = Mage::helper('bronto_reminder')->getSendFailureThreshold();

        $currentDate = Mage::getModel('core/date')->date('Y-m-d');
        $rules = $this->getCollection()->addDateFilter($currentDate)
            ->addIsActiveFilter(1);

        if ($ruleId = $this->getRuleId()) {
            $rules->addRuleFilter($ruleId);
        }

        foreach ($rules as $rule) {
            $this->_getResource()->deactivateMatchedCustomers($rule->getId());

            if ($rule->getSalesruleId()) {
                /* @var $salesRule Mage_SalesRule_Model_Rule */
                $salesRule = Mage::getSingleton('salesrule/rule')->load($rule->getSalesruleId());
                $websiteIds = array_intersect($rule->getWebsiteIds(), $salesRule->getWebsiteIds());
            } else {
                $salesRule = null;
                $websiteIds = $rule->getWebsiteIds();
            }

            $rule->setConditions(null);
            $rule->afterLoad();

            foreach ($websiteIds as $websiteId) {
                $this->_getResource()->saveMatchedCustomers($rule, $salesRule, $websiteId, $threshold);
            }
        }

        return $this;
    }

    /**
     * Return Message data
     *
     * @param int $ruleId
     * @param int $storeId
     * @return array|false
     */
    public function getMessageData($ruleId, $storeId)
    {
        if (!isset($this->_messageData[$ruleId][$storeId])) {
            if ($data = $this->_getResource()->getStoreMessageData($ruleId, $storeId)) {
                if (empty($data['message_id'])) {
                    $data['message_id'] = null;
                }
                $this->_messageData[$ruleId][$storeId] = $data;
            } else {
                return false;
            }
        }
        return $this->_messageData[$ruleId][$storeId];
    }

    /**
     * @param int    $ruleId
     * @param int    $customerId
     * @param string $messageId
     * @return boolean|array
     */
    public function getRuleLogItems($ruleId, $customerId, $messageId = null)
    {
        if ($data = $this->_getResource()->getRuleLogItemsData($ruleId, $customerId, $messageId)) {
            return $data;
        }
        return false;
    }

    /**
     * Returns an array containing information for sending abandoned cart notifications
     * to guest users who abandoned the checkout process.
     *
     * @return array
     */
    private function _getGuestAbandons()
    {
        $guestAbandons = Mage::getModel('bronto_reminder/guest')
            ->getCollection()
            ->addFieldToFilter('email_sent', array('eq' => 0));
        $retVal = array();
        $ruleId = null;
        $couponId = null;
        $schedule = null;

        $currentDate = Mage::getModel('core/date')->date('Y-m-d');

        $rules = $this->getCollection()->addDateFilter($currentDate)
            ->addIsActiveFilter(1);

        if ($ruleId = $this->getRuleId()) {
            $rules->addRuleFilter($ruleId);
        }

        foreach($guestAbandons as $guest) {
            foreach ($rules as $rule) {
                $this->_getResource()->deactivateMatchedCustomers($rule->getId());

                if ($rule->getSalesruleId()) {
                    /* @var $salesRule Mage_SalesRule_Model_Rule */
                    $salesRule = Mage::getSingleton('salesrule/rule')->load($rule->getSalesruleId());
                    $websiteIds = array_intersect($rule->getWebsiteIds(), $salesRule->getWebsiteIds());
                    $coupon = $salesRule->acquireCoupon();
                    $couponId = ($coupon !== null) ? $coupon->getId() : null;
                } else {
                    $salesRule = null;
                    $websiteIds = $rule->getWebsiteIds();
                }

                $rule->setConditions(null);
                $rule->afterLoad();

                foreach ($websiteIds as $websiteId) {
                    // Saving matched guests
                    $retVal[] = array(
                        'customer_id' => 0,
                        'rule_id' => $rule->getId(),
                        'coupon_id' => $couponId,
                        'schedule' => $schedule,
                        // Additional index for guest abandons used to create the
                        // Magento Customer object when sending reminder emails
                        'guest' => $guest,
                    );
                }

                $couponId = null;
            }
        }

        return $retVal;
    }
}
