<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Block_Adminhtml_Widget_Grid_Column_Renderer_Id extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render customer id linked to its account edit page
     *
     * @param Varien_Object $row
     * @return string
     */
    protected function _getValue(Varien_Object $row)
    {
        $customerId = $this->htmlEscape($row->getData($this->getColumn()->getIndex()));
        if (is_null($customerId)) {
            return 'Guest';
        }
        return '<a href="' . Mage::getSingleton('adminhtml/url')->getUrl('*/customer/edit',
            array('id' => $customerId)) . '">' . $customerId . '</a>';
    }
}
