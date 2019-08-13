<?php

class Brontosoftware_Connector_Model_Impl_Connector_Queue implements Brontosoftware_Magento_Connector_QueueManagerInterface
{
    /**
     * @see parent
     */
    public function hasItems($siteId)
    {
        return Mage::getModel('brontosoftware_connector/queue')
            ->getCollection()
            ->addFieldToFilter('site_id', array('eq' => $siteId))
            ->getSize() > 0;
    }

    /**
     * @see parent
     */
    public function save($event)
    {
        $eventType = $event['data']['type'];
        $queue = Mage::getModel('brontosoftware_connector/queue');
        // In the event a queued event is using a unique key, load it and adjust timestamp
        if (array_key_exists('context', $event['data']) && array_key_exists('event', $event['data']['context'])) {
            if (array_key_exists('uniqueKey', $event['data']['context']['event'][$eventType])) {
                $eventType = $event['data']['context']['event'][$eventType]['uniqueKey'];
                $queue->loadByEventType($event['data']['account']['siteId'], $eventType);
            }
        }
        $queue->setEventType($eventType)
            ->setSiteId($event['data']['account']['siteId'])
            ->setEventData(serialize($event))
            ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
            ->save();
        return true;
    }

    /**
     * @see parent
     */
    public function delete(Brontosoftware_Magento_Connector_QueueInterface $event)
    {
        $event->delete();
        return true;
    }

    /**
     * @see parent
     */
    public function deleteByIds(array $queueIds)
    {
        $entries = Mage::getModel('brontosoftware_connector/queue')
            ->getCollection()
            ->addFieldToFilter('queue_id', array('in' => $queueIds));
        foreach ($entries as $entry) {
            $this->delete($entry);
        }
    }

    /**
     * @see parent
     */
    public function getOldestEvents($siteId, $limit = null, $type = null)
    {
        if (is_null($limit)) {
            $limit = self::LIMIT;
        }
        $entries = Mage::getModel('brontosoftware_connector/queue')
            ->getCollection()
            ->addFieldToFilter('site_id', array('eq' => $siteId));
        if (!is_null($type)) {
            $entries->addFieldToFilter('event_type', array('eq' => $type));
        }
        $entries->setPageSize($limit)->setOrder('created_at', 'ASC');
        return $entries;
    }
}
