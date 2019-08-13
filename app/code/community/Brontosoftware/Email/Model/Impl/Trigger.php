<?php

class Brontosoftware_Email_Model_Impl_Trigger implements Brontosoftware_Magento_Email_TriggerManagerInterface
{
    protected $_customerRepo;
    protected $_logger;
    protected $_integration;
    protected $_orderSettings;
    protected $_currencies;
    protected $_emailSettings;
    protected $_productRepo;
    protected $_scoped;
    protected $_stockManager;
    protected $_addressRender;
    protected $_urls;

    /**
     * Inject dependencies
     */
    public function __construct()
    {
        $this->_currencies = Mage::getModel('brontosoftware_connector/impl_core_currency');
        $this->_orderSettings = Mage::getModel('brontosoftware_order/settings');
        $this->_emailSettings = Mage::getModel('brontosoftware_email/settings');
        $this->_productRepo = Mage::getModel('brontosoftware_connector/impl_core_productCacheBridge');
        $this->_integration = Mage::getModel('brontosoftware_integration/settings');
        $this->_customerRepo = Mage::getModel('brontosoftware_connector/impl_core_customer');
        $this->_logger = Mage::getModel('brontosoftware_connector/impl_core_logger');
        $this->_addressRender = Mage::getModel('brontosoftware_connector/impl_core_addressRender');
        $this->_urls = Mage::getSingleton('brontosoftware_connector/impl_core_urls');
        $this->_scoped = Mage::getSingleton('brontosoftware_connector/impl_core_scoped');
        $this->_stockManager = Mage::getSingleton('brontosoftware_connector/impl_core_stock');
    }

    /**
     * @see parent
     */
    public function hasItems($siteId)
    {
        return $this->_latestTriggers($siteId)->getSize() > 0;
    }

    /**
     * @see parent
     */
    public function save(Brontosoftware_Magento_Email_TriggerInterface $trigger)
    {
        $trigger->save();
    }

    /**
     * @see parent
     */
    public function getTriggers($siteId, $modelType, $modelId)
    {
        $results = array();
        $collection = Mage::getModel('brontosoftware_email/trigger')
            ->getCollection()
            ->addFieldToFilter('site_id', array('eq' => $siteId))
            ->addFieldToFilter('model_type', array('eq' => $modelType))
            ->addFieldToFilter('model_id', array('eq' => $modelId));
        foreach ($collection as $result) {
            $results[$result->getMessageId()] = $result;
        }
        return $results;
    }

    /**
     * @see parent
     */
    public function createTrigger($siteId, $messageType, $messageId)
    {
        return Mage::getModel('brontosoftware_email/trigger')
            ->setSentMessage(0)
            ->setSiteId($siteId)
            ->setMessageType($messageType)
            ->setMessageId($messageId);
    }

    /**
     * @see parent
     */
    public function getApplicableTriggers($siteId, $customerEmail = null, $limit = null, $messageType = null)
    {
        if (is_null($limit)) {
            $limit = self::LIMIT;
        }
        $collection = $this->_latestTriggers($siteId, $customerEmail)->setPageSize($limit);
        if (!is_null($messageType)) {
            $collection->addFieldToFilter('message_type', array('eq' => $messageType));
        }
        return $collection;
    }

    /**
     * @see parent
     */
    public function delete(Brontosoftware_Magento_Email_TriggerInterface $trigger)
    {
        $trigger->delete();
    }

    /**
     * @see parent
     */
    public function deleteExpiredItems($siteId, $daysInthePast = null)
    {
        if (is_null($daysInthePast)) {
            $daysInthePast = self::DAYS_THRESHOLD;
        }
        $newTime = strtotime("-{$daysInthePast} days");
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();
        $tableName = Mage::getModel('brontosoftware_email/trigger')->getResource()->getMainTable();
        $connection->delete($tableName, array(
            $connection->quoteInto('site_id = ?', $siteId),
            $connection->quoteInto('triggered_at < ?', date('Y-m-d H:i:s', $newTime))
        ));
        $connection->commit();
    }

    /**
     * @see parent
     */
    public function createSource($trigger, $message)
    {
        switch ($trigger->getMessageType()) {
        case 'review':
            return new Brontosoftware_Magento_Email_Event_Trigger_Review($this->_emailSettings, $this->_currencies, $trigger, $this->_orderSettings, $this->_scoped, $this->_addressRender, $this->_urls, $message);
        case 'reorder':
            return new Brontosoftware_Magento_Email_Event_Trigger_Reorder($this->_stockManager, $this->_emailSettings, $this->_currencies, $trigger, $this->_orderSettings, $this->_scoped, $this->_addressRender, $message);
        case 'caretip':
            return new Brontosoftware_Magento_Email_Event_Trigger_Caretip($this->_emailSettings, $this->_currencies, $trigger, $this->_orderSettings, $this->_scoped, $this->_addressRender, $message);
        case 'cart':
            return new Brontosoftware_Magento_Email_Event_Trigger_Cart($this->_integration, $this->_emailSettings, $this->_currencies, $trigger, $this->_orderSettings, $this->_scoped, $message);
        case 'wishlist':
            return new Brontosoftware_Magento_Email_Event_Trigger_Wishlist($this->_logger, $this->_integration, $this->_customerRepo, $this->_productRepo, $this->_emailSettings, $this->_currencies, $trigger, $this->_orderSettings, $this->_scoped, $message);
        default:
            return Mage::getModel('brontosoftware_email/event_source');
        }
    }

    /**
     * Gets triggers that haven't been sent yet
     *
     * @param string $siteId
     * @param string $customerEmail
     * @return mixed
     */
    protected function _latestTriggers($siteId, $customerEmail = null)
    {
        $collection = Mage::getModel('brontosoftware_email/trigger')
            ->getCollection()
            ->addFieldToFilter('site_id', array('eq' => $siteId))
            ->addFieldToFilter('sent_message', array('eq' => '0'));
        if (empty($customerEmail)) {
            $nowGmt = date('Y-m-d H:i:s');
            $collection->addFieldToFilter('triggered_at', array('lt' => $nowGmt));
        } else {
            $collection->addFieldToFilter('customer_email', array('eq' => $customerEmail));
        }
        return $collection;
    }
}
