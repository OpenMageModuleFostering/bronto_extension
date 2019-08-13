<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.7
 */
class Bronto_Order_Model_Observer
{
    const NOTICE_IDENTIFER = 'bronto_order';

    /**
     * @param Varien_Event_Observer $observer
     */
    public function checkBrontoRequirements(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return;
        }

        // Verify Requirements
        if (!Mage::helper(self::NOTICE_IDENTIFER)->varifyRequirements(self::NOTICE_IDENTIFER, array('soap', 'openssl'))) {
            return;
        }
    }

    /**
     * @param mixed $storeId
     * @return array
     */
    public function processOrdersForStore($storeId = null)
    {
        if (is_object($storeId)) {
            $store   = $storeId;
            $storeId = $store->getId();
        } else {
            $store   = Mage::app()->getStore($storeId);
            $storeId = $store->getId();
        }

        $result = array('total' => 0, 'success' => 0, 'error' => 0);
        Mage::helper('bronto_order')->writeDebug("Starting Order Import process for store: {$store->getName()} ({$storeId})");

        if (!$store->getConfig(Bronto_Order_Helper_Data::XML_PATH_ENABLED)) {
            Mage::helper('bronto_order')->writeDebug('  Module disabled for this store. Skipping...');
            return $result;
        }

        // Retrieve Store's configured API Token
        $token = $store->getConfig(Bronto_Common_Helper_Data::XML_PATH_API_TOKEN);

        /* @var $api Bronto_Common_Model_Api */
        $api = Mage::helper('bronto_order')->getApi($token);

        /* @var $orderObject Bronto_Api_Order */
        $orderObject = $api->getOrderObject();

        $limit = $store->getConfig(Bronto_Order_Helper_Data::XML_PATH_LIMIT);
        if (!$limit) {
            Mage::helper('bronto_order')->writeDebug('  Limit empty. Skipping...');
            return $result;
        }

        $orderRows = Mage::getModel('bronto_order/queue')
            ->getCollection()
            ->addBrontoNotImportedFilter()
            ->orderByUpdatedAt()
            ->setPageSize($limit)
            ->addStoreFilter($storeId)
            ->getItems();
        
        if (empty($orderRows)) {
            Mage::helper('bronto_order')->writeVerboseDebug('  No Orders to process. Skipping...');
            return $result;
        }

        /* @var $productHelper Bronto_Common_Helper_Product */
        $productHelper   = Mage::helper('bronto_common/product');
        $descriptionAttr = $store->getConfig(Bronto_Order_Helper_Data::XML_PATH_DESCRIPTION);
        $orderCache      = array();

        foreach ($orderRows as $orderRow) {
            $orderId = $orderRow->getOrderId();
            if ($order = Mage::getModel('sales/order')->load($orderId) /* @var $order Mage_Sales_Model_Order */) {
                Mage::helper('bronto_order')->writeDebug("  Processing Order ID: {$orderId}");
                $orderCache[] = $orderId;

                /* @var $brontoOrder Bronto_Api_Order_Row */
                $brontoOrder = $orderObject->createRow();
                $brontoOrder->email     = $order->getCustomerEmail();
                $brontoOrder->id        = $order->getIncrementId();
                $brontoOrder->orderDate = date('c', strtotime($order->getCreatedAt()));
                if ($tid = $orderRow->getBrontoTid()) {
                    $brontoOrder->tid = $tid;
                }
                $brontoOrderItems = array();

                switch ($order->getState()) {
                    case Mage_Sales_Model_Order::STATE_CANCELED:
                    case Mage_Sales_Model_Order::STATE_HOLDED:
                    case Mage_Sales_Model_Order::STATE_CLOSED:
                        $brontoOrder->delete();
                        break;

                    default:
                        // loop through the items. if it's a bundled item, replace the parent item with the child items.
                        $items = $order->getAllVisibleItems();
                        $i = 0;
                        foreach ($items as $item) {
                            if (count($item->getChildrenItems()) > 0) {
                                unset($items[0]);
                                foreach ($item->getChildrenItems() as $child_item) {
                                    $items[] = $child_item;
                                }
                            }
                            $i++;
                        }

                        foreach ($items as $item /* @var $item Mage_Sales_Model_Order_Item */) {
                            /* @var $product Mage_Catalog_Model_Product */
                            $product     = Mage::getModel('catalog/product')->load($item->getProductId());
                            $categoryIds = $product->getCategoryIds();
                            $categories  = array();
                            foreach ($categoryIds as $categoryId) {
                                /* @var $category Mage_Catalog_Model_Category */
                                $category     = Mage::getModel('catalog/category')->load($categoryId);
                                $parent       = $category->getParentCategory();
                                $categories[] = $parent->getUrlKey() ? $parent->getUrlKey() : $parent->formatUrlKey($parent->getName());
                                $categories[] = $category->getUrlKey() ? $category->getUrlKey() : $category->formatUrlKey($category->getName());
                            }
                            $categories = array_unique($categories);

                            // Write orderItem
                            $brontoOrderItems[] = array(
                                'id'          => $item->getId(),
                                'sku'         => $item->getSku(),
                                'name'        => $item->getName(),
                                'description' => $productHelper->getProductAttribute($item->getProductId(), $descriptionAttr),
                                'category'    => implode(' ', $categories),
                                'image'       => $productHelper->getProductAttribute($item->getProductId(), 'image'),
                                'url'         => $productHelper->getProductAttribute($item->getProductId(), 'url'),
                                'quantity'    => (int)   $item->getQtyOrdered(),
                                'price'       => (float) $item->getPrice(),
                            );
                        }
                        $brontoOrder->products = $brontoOrderItems;
                        $brontoOrder->persist();
                        break;
                }

                try {
                    // Mark order as imported
                    $orderRow->setBrontoImported(Mage::getSingleton('core/date')->gmtDate());
                    $orderRow->save();
                    
                    // Flush every 10 orders
                    if ($result['total'] % 100 === 0) {
                        $result     = $this->flushOrders($orderObject, $orderCache, $result);
                        $orderCache = array();
                    }
                } catch (Exception $e) {
                    Mage::helper('bronto_order')->writeError($e);

                    // Mark import as *not* imported
                    $orderRow->setBrontoImported(null);
                    $orderRow->save();

                    $result['error']++;
                }

                $result['total']++;
            }
        }
        
        // Final flush (for any we miss)
        $results = $this->flushOrders($orderObject, $orderCache, $result);

        Mage::helper('bronto_order')->writeDebug('  Success: ' . $results['success']);
        Mage::helper('bronto_order')->writeDebug('  Error:   ' . $results['error']);
        Mage::helper('bronto_order')->writeDebug('  Total:   ' . $results['total']);

        return $results;
    }

    /**
     * @param Bronto_Api_Order $orderObject
     * @param array            $orderCache
     * @param array            $result
     * @return array
     */
    public function flushOrders($orderObject, $orderCache, $result)
    {
        $flushResult = $orderObject->flush();

        Mage::helper('bronto_order')->writeVerboseDebug('===== FLUSH =====', 'bronto_order_api.log');
        Mage::helper('bronto_order')->writeVerboseDebug(var_export($orderObject->getApi()->getLastRequest(), true), 'bronto_order_api.log');
        Mage::helper('bronto_order')->writeVerboseDebug(var_export($orderObject->getApi()->getLastResponse(), true), 'bronto_order_api.log');

        foreach ($flushResult as $i => $flushResultRow) {
            if ($flushResultRow->hasError()) {
                $errorCode    = $flushResultRow->getErrorCode();
                $errorMessage = $flushResultRow->getErrorMessage();
                if (isset($orderCache[$i])) {
                    // Get Order Object
                    $order = Mage::getModel('sales/order')->load($orderCache[$i]);
                    
                    // Reset Bronto Import status
                    $orderRow = Mage::getModel('bronto_order/queue')
                        ->getOrderRow($order->getId(), $order->getQuoteId(), $order->getStoreId())
                        ->setBrontoImported(null)
                        ->save();
                    
                    Mage::helper('bronto_order')->writeError("[{$errorCode}] {$errorMessage} ({$order->getIncrementId})");
                } else {
                    Mage::helper('bronto_order')->writeError("[{$errorCode}] {$errorMessage}");
                }
                $result['error']++;
            } else {
                $result['success']++;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function processOrders()
    {
        $result = array(
            'total'   => 0,
            'success' => 0,
            'error'   => 0,
        );

        $stores = Mage::app()->getStores(true);
        foreach ($stores as $_store) {
            $storeResult = $this->processOrdersForStore($_store);
            $result['total']   += $storeResult['total'];
            $result['success'] += $storeResult['success'];
            $result['error']   += $storeResult['error'];
        }

        return $result;
    }
}
