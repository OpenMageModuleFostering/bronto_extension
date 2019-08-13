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
            ->getSize()
        ;
    }

    /**
     * @return Bronto_Customer_Model_Resource_Customer_Collection
     */
    protected function getCustomerResourceCollection()
    {
        $collection = Mage::getModel('bronto_customer/resource_customer_collection');

        if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
            $store = Mage::app()->getStore($storeCode);
            $collection->addStoreFilter($store->getId());
        } else if ($websiteCode = Mage::app()->getRequest()->getParam('website')){
            $website = Mage::app()->getWebsite($websiteCode);
            $collection->addStoreFilter($website->getStoreids());
        } else if ($groupCode = Mage::app()->getRequest()->getParam('group')){
            $website = Mage::app()->getGroup($groupCode)->getWebsite();
            $collection->addStoreFilter($website->getStoreids());
        }

        return $collection;
    }
}
