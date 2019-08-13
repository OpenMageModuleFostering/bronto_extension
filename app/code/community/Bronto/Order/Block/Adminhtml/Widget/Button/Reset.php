<?php

/**
 * @package   Order
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.1.5
 */
class Bronto_Order_Block_Adminhtml_Widget_Button_Reset extends Mage_Adminhtml_Block_Widget_Button
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
        $this->setLabel('Reset All Orders');
        $this->setOnClick("deleteConfirm('This will mark all orders as not-imported and will cause the importer to re-process each order again.\\n\\nAre you sure you want to do this?', '" . Mage::helper('adminhtml')->getUrl('*/order/reset', $params) . "'); return false;");
        $this->setClass('delete');

        if (!extension_loaded('soap') || !extension_loaded('openssl') || !Mage::helper('bronto_common')->getApiToken() || !Mage::helper('bronto_order')->isEnabled() || (!Mage::helper('bronto_order')->isDebugEnabled() && !Mage::helper('bronto_order')->isTestModeEnabled())) {
            $this->setDisabled(true)->setClass('disabled');
        }
    }
}
