<?php

class Brontosoftware_Connector_Block_Adminhtml_Registration_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * @see parent
     */
    public function render(Varien_Object $row)
    {
        $actions = array();
        $actions[] = array(
            'url' => $this->getUrl('*/*/edit', array('id' => $row->getId())),
            'caption' => $this->__('Edit'),
        );

        $actions[] = array(
            'url' => $this->getUrl('*/*/delete', array('id' => $row->getId())),
            'caption' => $this->__('Delete'),
            'confirm' => $this->__('Are you sure you want to delete the selected registration?')
        );

        $actions[] = array(
            'url' => $this->getUrl('*/*/sync', array('id' => $row->getId())),
            'caption' => $this->__('Sync Settings'),
        );
        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}
