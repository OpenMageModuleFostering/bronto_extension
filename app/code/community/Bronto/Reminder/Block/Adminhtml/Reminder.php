<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Block_Adminhtml_Reminder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @var string
     */
    protected $_controller = 'adminhtml_reminder';

    /**
     * @var string
     */
    protected $_blockGroup = 'bronto_reminder';

    public function __construct()
    {
        $this->_headerText = Mage::helper('bronto_reminder')->__('Bronto Reminder Email Rules');
        $this->_addButtonLabel = Mage::helper('bronto_reminder')->__('Add New Rule');
        parent::__construct();
        $this->setTemplate('bronto/reminder/grid/container.phtml');
    }

    /**
     * Get link to transactional email configuration
     * @return type
     */
    public function getConfigLink()
    {
        return Mage::helper($this->_blockGroup)->getConfigLink();
    }
}
