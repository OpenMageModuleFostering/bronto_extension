<?php

class Brontosoftware_Connector_Block_Adminhtml_Registration extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_controller = 'adminhtml_registration';
    protected $_blockGroup = 'brontosoftware_connector';
    protected $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper($this->_blockGroup);
        $this->_headerText = $this->_helper->__('Bronto Connector Registrations');
        parent::__construct();
    }
}
