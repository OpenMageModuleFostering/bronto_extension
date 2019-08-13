<?php

/**
 * @package     Bronto\Newsletter
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.1.5
 */
class Bronto_Newsletter_Block_Adminhtml_Widget_Button_Reset extends Mage_Adminhtml_Block_Widget_Button
{
    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $this->setLabel('Reset All Subscribers');
        $this->setOnClick("deleteConfirm('This will mark all subscribers as not-imported and will cause the importer to re-process each subscriber again.\\n\\nAre you sure you want to do this?', '" . Mage::helper('adminhtml')->getUrl('*/newsletter/reset') . "'); return false;");
        $this->setClass('delete');

        if (!extension_loaded('soap') || !extension_loaded('openssl') || !Mage::helper('bronto_common')->getApiToken() || !Mage::helper('bronto_customer')->isEnabled() || (!Mage::helper('bronto_customer')->isDebugEnabled() && !Mage::helper('bronto_customer')->isTestModeEnabled())) {
            $this->setDisabled(true)->setClass('disabled');
        }
    }
}
