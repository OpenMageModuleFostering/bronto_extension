<?php

class Brontosoftware_Connector_Model_Queue extends Mage_Core_Model_Abstract implements Brontosoftware_Magento_Connector_QueueInterface
{
    /**
     * @see parent
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('brontosoftware_connector/queue');
    }

    /**
     * Attempts to load the event by queue type
     *
     * @param string $siteId
     * @param string $eventType
     * @return $this
     */
    public function loadByEventType($siteId, $eventType)
    {
        $this->getResource()->loadByEventType($this, $siteId, $eventType);
        return $this;
    }

    /**
     * @see parent
     */
    public function getEventType()
    {
        return $this->getData(self::EVENT_TYPE);
    }

    /**
     * @see parent
     */
    public function getEventData()
    {
        return $this->getData(self::EVENT_DATA);
    }

    /**
     * @see parent
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @see parent
     */
    public function getSiteId()
    {
        return $this->getData(self::SITE_ID);
    }
}
