<?php

/**
 * @package     Bronto\Customer
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.5
 */
class Bronto_Customer_Block_Adminhtml_System_Config_Cron extends Bronto_Common_Block_Adminhtml_System_Config_Cron
{
    protected $_jobCode        = 'bronto_customer_import';
    protected $_hasProgressBar = true;

    /**
     * @return Bronto_Order_Block_Adminhtml_System_Config_Cron
     */
    protected function _prepareLayout()
    {
        $missingCustomers = $this->helper('bronto_customer')->getMissingCustomers(true);        
        if ($missingCustomers > 0) {
            $this->addButton($this->getLayout()->createBlock('bronto_customer/adminhtml_widget_button_sync'));
        }
        $this->addButton($this->getLayout()->createBlock('bronto_customer/adminhtml_widget_button_reset'));
        $this->addButton($this->getLayout()->createBlock('bronto_customer/adminhtml_widget_button_run'));

        return parent::_prepareLayout();
    }

    /**
     * @return int
     */
    protected function getProgressBarTotal()
    {
        return $this->getCustomerResourceCollection()
            ->addBrontoNotSuppressedFilter()
            ->getSize()
        ;
    }

    /**
     * @return int
     */
    protected function getProgressBarPending()
    {
        return $this->getCustomerResourceCollection()
            ->addBrontoNotImportedFilter()
            ->addBrontoNotSuppressedFilter()
            ->getSize();
    }

    /**
     * @return Bronto_Customer_Model_Mysql4_Queue_Collection
     */
    protected function getCustomerResourceCollection()
    {
        $collection = Mage::getModel('bronto_customer/queue')->getCollection();
        $storeIds   = Mage::helper('bronto_customer')->getStoreIds();
        
        if ($storeIds) {
            $collection->addStoreFilter($storeIds);
        }

        return $collection;
    }
}
