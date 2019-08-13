<?php

/**
 * @package     Bronto\Customer
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.1.5
 */
class Bronto_Customer_Block_Adminhtml_Widget_Button_Run extends Mage_Adminhtml_Block_Widget_Button
{
    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $this->setLabel('Run Now');
        $this->setOnClick("setLocation('" . Mage::helper('adminhtml')->getUrl('*/customer/run') . "'); return false;");

        if (!extension_loaded('soap') || !extension_loaded('openssl') || !Mage::helper('bronto_common')->getApiToken() || !Mage::helper('bronto_customer')->isEnabled()) {
            $this->setDisabled(true)->setClass('disabled');
        }
    }
}
