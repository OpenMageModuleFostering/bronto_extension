<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Block_Adminhtml_System_Email_Template_Grid_Renderer_Message extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/bronto/';

        if ($row->getBrontoMessageApproved()) {
            return '
                <img src="' . $skinUrl . 'images/message_approved.gif" style="vertical-align:top;padding-right:1px" />
                <strong>Approved!</strong>
            ';
        } else {
            return '
                <img src="' . $skinUrl . 'images/message_not_approved.gif" style="vertical-align:top;padding-right:1px" />
                <strong>Not Approved!</strong>
            ';
        }
    }
}
