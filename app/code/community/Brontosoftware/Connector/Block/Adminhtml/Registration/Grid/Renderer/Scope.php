<?php

class Brontosoftware_Connector_Block_Adminhtml_Registration_Grid_Renderer_Scope extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @see parent
     */
    public function render(Varien_Object $row)
    {
        switch ($row->getScope()) {
        case 'store':
            $model = Mage::app()->getStore($row->getScopeCode());
            break;
        case 'website':
            $model = Mage::app()->getStore($row->getScopeCode());
            break;
        default:
            $model = new Varien_Object(array('name' => $this->__('Default')));
        }
        return htmlentities($model->getName());
    }
}
