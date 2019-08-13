<?php

class Brontosoftware_Email_Model_Mysql4_Trigger extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * @see parent
     */
    protected function _construct()
    {
        $this->_init('brontosoftware_email/trigger', 'trigger_id');
    }
}
