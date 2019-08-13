<?php

class Brontosoftware_Connector_Block_Adminhtml_Registration_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_controller = 'registration';
    protected $_blockGroup = 'brontosoftware_connector';

    /**
     * @see parent
     */
    public function getHeaderText()
    {
        $currentId = $this->getRequest()->getParam($this->_objectId, null);
        if ($currentId) {
            return $this->__('Edit Connector Registration');
        } else {
            return $this->__('New Connector Registration');
        }
    }
}
