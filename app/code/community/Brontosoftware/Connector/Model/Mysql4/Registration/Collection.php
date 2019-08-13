<?php

class Brontosoftware_Connector_Model_Mysql4_Registration_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @see parent
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('brontosoftware_connector/registration');
    }

    /**
     * Filters the registration by active
     *
     * @param boolean $active
     * @return array
     */
    public function filterByActive($active = true)
    {
        return $this->addFieldToFilter('is_active', array('eq' => $active));
    }
}
