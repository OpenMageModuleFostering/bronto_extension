<?php

/**
 * @package     Bronto\Newsletter
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.3.5
 */
class Bronto_Newsletter_Block_Adminhtml_System_Config_Cron extends Bronto_Common_Block_Adminhtml_System_Config_Cron
{
    protected $_jobCode        = 'bronto_newsletter_import';
    protected $_hasProgressBar = false;

    /**
     * @return Bronto_Order_Block_Adminhtml_System_Config_Cron
     */
    protected function _prepareLayout()
    {
        $this->addButton($this->getLayout()->createBlock('bronto_newsletter/adminhtml_widget_button_reset'));
        $this->addButton($this->getLayout()->createBlock('bronto_newsletter/adminhtml_widget_button_run'));

        return parent::_prepareLayout();
    }

    /**
     * @return int
     */
    protected function getProgressBarTotal()
    {
        return $this->getOrderResourceCollection()
            ->addStoreFilter($storeId)
            ->getSize()
        ;
    }

    /**
     * @return int
     */
    protected function getProgressBarPending()
    {
        return $this->getOrderResourceCollection()
            ->addBrontoNotImportedFilter()
            ->getSize()
        ;
    }

    /**
     * @return Bronto_Order_Model_Resource_Order_Collection
     */
    protected function getOrderResourceCollection()
    {
        $collection = Mage::getModel('bronto_order/resource_order_collection');

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
