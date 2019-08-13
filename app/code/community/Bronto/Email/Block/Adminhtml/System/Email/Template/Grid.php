<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Block_Adminhtml_System_Email_Template_Grid extends Mage_Adminhtml_Block_System_Email_Template_Grid
{
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Core_Model_Resource_Email_Template_Collection */
        $collection = Mage::getResourceSingleton('core/email_template_collection');

        if (!Mage::helper('bronto_email')->isEnabled()) {
            $collection->addFieldToFilter('bronto_message_id', array('null' => true));
        } else {
            $collection->addFieldToFilter('bronto_message_id', array('notnull' => true));
        }

        //  Change how table names and SQL aliases are mapped resource
        //  to account for version 1.9 and 1.10 differences
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('core/store');
        $mainTable = $resource->getTableName('core/email_template');
        $version = Mage::getVersionInfo();
        
        if (1 == $version['major'] && 9 != $version['minor'] && 10 != $version['minor']) {
            $mainTable = 'main_table';
        }
        $collection->getSelect()
                   ->join(
                       array('core_store' => $tableName),
                       "`$mainTable`.store_id=`core_store`.store_id",
                       array('storename' => 'core_store.name')
                   );

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Add columns to the grid
     *
     * @return Bronto_Email_Block_Adminhtml_System_Email_Template_Grid
     */
    protected function _prepareColumns()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::_prepareColumns();
        }

        $this->addColumn(
            'template_id',
            array(
                'header' => Mage::helper('adminhtml')->__('ID'),
                'index' => 'template_id'
            )
        );

        $this->addColumn(
            'added_at',
            array(
                'header' => Mage::helper('adminhtml')->__('Date Added'),
                'index' => 'added_at',
                'gmtoffset' => true,
                'type' => 'datetime'
            )
        );

        $this->addColumn(
            'modified_at',
            array(
                'header' => Mage::helper('adminhtml')->__('Date Updated'),
                'index' => 'modified_at',
                'gmtoffset' => true,
                'type' => 'datetime'
            )
        );

        $this->addColumn(
            'code',
            array(
                'header' => Mage::helper('adminhtml')->__('Name'),
                'index' => 'template_code'
            )
        );

        $this->addColumn(
            'message_name',
            array(
                'header' => Mage::helper('adminhtml')->__('Bronto Message'),
                'index' => 'bronto_message_name'
            )
        );

        $this->addColumn(
            'store',
            array(
                'header' => Mage::helper('adminhtml')->__('Store'),
                'index' => 'storename'
            )
        );

        return $this;
    }
}
