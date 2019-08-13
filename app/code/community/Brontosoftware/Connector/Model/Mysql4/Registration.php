<?php

class Brontosoftware_Connector_Model_Mysql4_Registration extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * @see parent
     */
    protected function _construct()
    {
        $this->_init('brontosoftware_connector/registration', 'entity_id');
    }

    /**
     * Loads the registration by scope and scope id
     *
     * @param Brontosoftware_Connector_Model_Registration $registration
     * @param string $scope
     * @param mixed $scopeId
     * @return void
     */
    public function loadByScope($model, $scope, $scopeId)
    {
        $read = $this->_getReadAdapter();
        $select = $this->_getLoadSelect('scope', $scope, $model);
        $fieldName = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'scope_id'));
        $select->where("{$fieldName} = ?", $scopeId);
        $data = $read->fetchRow($select);
        if ($data) {
            $model->setData($data);
        }
    }
}
