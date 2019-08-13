<?php

class Brontosoftware_Connector_Block_Adminhtml_Registration_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @see parent
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('registrationGrid');
        $this->setIdFieldName('entity_id');
        $this->setDefaultSort('entity_id', 'asc');
    }

    /**
     * @see parent
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('brontosoftware_connector/registration')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @see parent
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('brontosoftware_connector');
        $options = array(
            0 => $helper->__('No'),
            1 => $helper->__('Yes')
        );
        $this->addColumn('entity_id', array(
            'header' => $helper->__('ID'),
            'align' => 'right',
            'width' => '150px',
            'index' => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header' => $helper->__('Name'),
            'align' => 'left',
            'index' => 'name',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('environment', array(
            'header' => $helper->__('Environment'),
            'align' => 'left',
            'index' => 'environment',
        ));

        $this->addColumn('connector_key', array(
            'header' => $helper->__('Account ID'),
            'align' => 'left',
            'index' => 'connector_key',
            'sortable' => false,
        ));

        $this->addColumn('is_active', array(
            'header' => $helper->__('Registered'),
            'align' => 'left',
            'index' => 'is_active',
            'type' => 'options',
            'width' => '150px',
            'options' => $options,
        ));

        $this->addColumn('updated_at', array(
            'header' => $helper->__('Updated At'),
            'index' => 'updated_at',
            'align' => 'left',
            'type' => 'datetime',
            'filter' => false,
            'sortable' => true,
        ));

        $this->addColumn('scope', array(
            'header' => $helper->__('Scope'),
            'align' => 'left',
            'renderer' => 'brontosoftware_connector/adminhtml_registration_grid_renderer_scope',
            'type' => 'text',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('action', array(
            'header' => $helper->__('Action'),
            'index' => 'entity_id',
            'sortable' => false,
            'filter' => false,
            'width' => '130px',
            'renderer' => 'brontosoftware_connector/adminhtml_registration_grid_renderer_action',
        ));

        return parent::_prepareColumns();
    }

    /**
     * @see parent
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/edit', array('id' => $item->getEntityId()));
    }
}
