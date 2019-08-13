<?php

/**
 * @package     Bronto\Newsletter
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.3.5
 */
class Bronto_Newsletter_Block_Adminhtml_System_Config_Cron extends Bronto_Common_Block_Adminhtml_System_Config_Cron
{
    protected $_jobCode        = 'bronto_newsletter_import';
    protected $_hasProgressBar = true;

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
        return $this->getNewsletterResourceCollection()
            ->getSize()
        ;
    }

    /**
     * @return int
     */
    protected function getProgressBarPending()
    {
        return $this->getNewsletterResourceCollection()
            ->addBrontoNotImportedFilter()
            ->getSize()
        ;
    }

    /**
     * @return Bronto_Newsletter_Model_Mysql4_Queue_Collection
     */
    protected function getNewsletterResourceCollection()
    {
        $collection = Mage::getModel('bronto_newsletter/queue')->getCollection();
        $storeIds   = Mage::helper('bronto_customer')->getStoreIds();
        
        if ($storeIds) {
            $collection->addStoreFilter($storeIds);
        }
        
        return $collection;
    }
}
