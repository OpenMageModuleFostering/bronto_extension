<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Block_Adminhtml_Widget_Grid_Column_Renderer_Email extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render customer email as mailto link
     *
     * @param Varien_Object $row
     * @return string
     */
    protected function _getValue(Varien_Object $row)
    {
        $customerEmail = $this->htmlEscape($row->getData($this->getColumn()->getIndex()));
        return '<a href="mailto:' . $customerEmail . '">' . $this->htmlEscape($customerEmail) . '</a>';
    }
}
