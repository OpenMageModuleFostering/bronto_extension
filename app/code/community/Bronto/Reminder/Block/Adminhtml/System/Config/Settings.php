<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Block_Adminhtml_System_Config_Settings
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header comment part of html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $url = Mage::helper('adminhtml')->getUrl('/reminders');
        $element->setComment("Additional configuration located at: <strong>Promotions &rsaquo; <a href=\"{$url}\">Bronto Reminder Emails</a></strong><br/><br/>");
        return parent::_getHeaderCommentHtml($element);
    }
}
