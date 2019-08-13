<?php

class Brontosoftware_Connector_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * @see parent
     */
    protected function _construct()
    {
        $this->_init('brontosoftware_connector/queue', 'queue_id');
    }

    /**
     * Attempts to load the event by queue type
     *
     * @param Brontosoftware_Connector_Model_Queue $model
     * @param string $siteId
     * @param string $eventType
     * @return void
     */
    public function loadByEventType($model, $siteId, $eventType)
    {
        $read = $this->_getReadAdapter();
        $select = $this->_getLoadSelect('site_id', $siteId, $model);
        $fieldName = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'event_type'));
        $select->where("{$fieldName} = ?", $eventType);
        $data = $read->fetchRow($select);
        if ($data) {
            $model->setData($data);
        }
    }
}
