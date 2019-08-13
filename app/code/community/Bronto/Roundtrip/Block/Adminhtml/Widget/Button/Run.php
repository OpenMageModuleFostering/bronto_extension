<?php

/**
 * @package   Roundtrip
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.1.5
 */
class Bronto_Roundtrip_Block_Adminhtml_Widget_Button_Run extends Mage_Adminhtml_Block_Widget_Button
{
    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $this->setLabel('Verify Now');
        $this->setOnClick("setLocation('" . Mage::helper('adminhtml')->getUrl('*/roundtrip/run') . "'); return false;");
    }
}
