<?php

class Brontosoftware_Browse_Block_Capture extends Mage_Core_Block_Template
{
    protected $_eventType = 'VIEW';

    /**
     * Sets the browse type for the eventuality of events
     *
     * @return $this
     */
    public function setEventType($eventType)
    {
        $this->_eventType = $eventType;
        return $this;
    }

    /**
     * Determines if this block is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        $currentStore = Mage::app()->getStore();
        $helper = Mage::getSingleton('brontosoftware_browse/settings');
        $enabled = $helper->isEnabled('store', $currentStore);
        if ($this->_eventType == 'SEARCH') {
            return $enabled && $helper->isSearchEnabled('store', $currentStore);
        }
        return $enabled;
    }

    /**
     * Gets the browse recovery url on the server
     *
     * @return string
     */
    public function getBrowseUrl()
    {
        $currentStore = Mage::app()->getStore();
        $params = array(
            '_secure' => $currentStore->isCurrentlySecure(),
            '_escape' => true,
            'event_type' => $this->_eventType
        );
        $product = Mage::registry('product');
        if ($product) {
            $params['id'] = $product->getId();
            $params['category_id'] = $product->getCategoryId();
        }
        $url = Mage::getModel('core/url')->setQueryParams($this->getRequest()->getQuery());
        return $url->getUrl('brontosoftware/browse/capture', $params);
    }
}
