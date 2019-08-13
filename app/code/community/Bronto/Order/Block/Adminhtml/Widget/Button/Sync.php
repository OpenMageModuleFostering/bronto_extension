<?php

/**
 * @package     Bronto\Order
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.1.8
 */
class Bronto_Order_Block_Adminhtml_Widget_Button_Sync extends Mage_Adminhtml_Block_Widget_Button
{
    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $missingOrders = $this->helper('bronto_order')->getMissingOrders(true);
                
        $this->setLabel(sprintf('Sync %d Orders to Queue', $missingOrders));
        $this->setOnClick("setLocation('" . Mage::helper('adminhtml')->getUrl('*/order/sync') . "'); return false;");
        $this->setClass('save');
        
        
        if ($missingOrders == 0) {
            $this->setLabel('Sync Complete');
            $this->setDisabled(true)->setClass('disabled');
        }

        if (!extension_loaded('soap') || !extension_loaded('openssl') || !Mage::helper('bronto_common')->getApiToken() || !Mage::helper('bronto_order')->isEnabled()) {
            $this->setDisabled(true)->setClass('disabled');
        }
    }
}
