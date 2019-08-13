<?php

class Brontosoftware_Browse_BrowseController extends Mage_Core_Controller_Front_Action
{
    const BROWSE_EVENT_NAME = 'brontosoftware_browse_event';

    /**
     * Generates a single browse event
     */
    public function captureAction()
    {
        $eventType = $this->getRequest()->getParam('event_type', 'VIEW');
        $currentStore = Mage::app()->getStore();
        $transform = '_' . strtolower($eventType) . 'Event';
        if (method_exists($this, $transform)) {
            $this->{$transform}($currentStore);
        }
    }

    /**
     * Generates a single product view event
     *
     * @param mixed $store
     * @return mixed
     */
    protected function _viewEvent($store)
    {
        $productId = (int) $this->getRequest()->getParam('id');
        $categoryId = (int) $this->getRequest()->getParam('category_id', null);
        $productRepo = Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge');
        $product = $productRepo->getById($productId, $store->getId());
        Mage::dispatchEvent(self::BROWSE_EVENT_NAME, array(
            'request' => $this->getRequest(),
            'product' => $product
        ));
    }

    /**
     * Generates an array of product search events
     *
     * @param mixed $store
     * @return mixed
     */
    protected function _searchEvent($store)
    {
        $layer = Mage::registry('current_layer');
        if (!$layer) {
            $layer = Mage::getSingleton('catalogsearch/layer');
        }
        $query = Mage::helper('catalogsearch')->getQuery();
        $collection = $layer->getProductCollection();
        foreach ($collection as $product) {
            Mage::dispatchEvent(self::BROWSE_EVENT_NAME, array(
                'product' => $product,
                'event_type' => 'SEARCH',
                'event_type_value' => $query->getQueryText()
            ));
        }
    }
}
