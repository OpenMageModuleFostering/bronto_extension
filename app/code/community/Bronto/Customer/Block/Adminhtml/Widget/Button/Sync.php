<?php

/**
 * @package     Bronto\Customer
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.1.5
 */
class Bronto_Customer_Block_Adminhtml_Widget_Button_Sync extends Mage_Adminhtml_Block_Widget_Button
{
    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $missingCustomers = $this->helper('bronto_customer')->getMissingCustomers(true);
                
        $this->setLabel(sprintf('Sync %d Contacts to Queue', $missingCustomers));
        $this->setOnClick("setLocation('" . Mage::helper('adminhtml')->getUrl('*/customer/sync') . "'); return false;");
        $this->setClass('save');
        
        
        if ($missingCustomers == 0) {
            $this->setLabel('Sync Complete');
            $this->setDisabled(true)->setClass('disabled');
        }

        if (!extension_loaded('soap') || !extension_loaded('openssl') || !Mage::helper('bronto_common')->getApiToken() || !Mage::helper('bronto_customer')->isEnabled()) {
            $this->setDisabled(true)->setClass('disabled');
        }
    }
}
