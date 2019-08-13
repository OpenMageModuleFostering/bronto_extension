<?php

class Brontosoftware_Email_Model_Mysql4_Trigger_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @see parent
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('brontosoftware_email/trigger');
    }
}
