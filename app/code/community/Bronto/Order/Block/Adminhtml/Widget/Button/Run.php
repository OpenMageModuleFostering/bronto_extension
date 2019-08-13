<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   2.0.0
 */
class Bronto_Order_Block_Adminhtml_Widget_Button_Run extends Mage_Adminhtml_Block_Widget_Button
{
    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $params = array(
            'section' => Mage::app()->getRequest()->getParam('section'),
            'website' => Mage::app()->getRequest()->getParam('website'),
            'store'   => Mage::app()->getRequest()->getParam('store'),
        );
        $this->setLabel('Run Now');
        $this->setOnClick("setLocation('" . Mage::helper('adminhtml')->getUrl('*/order/run', $params) . "'); return false;");

        if (!extension_loaded('soap') || !extension_loaded('openssl') || !Mage::helper('bronto_common')->getApiToken() || !Mage::helper('bronto_order')->isEnabled()) {
            $this->setDisabled(true)->setClass('disabled');
        }
    }
}
