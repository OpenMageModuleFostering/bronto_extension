<?php

/**
 * @package   Bronto\Order
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.1.7
 */
class Bronto_Order_Model_Observer
{

    const NOTICE_IDENTIFER = 'bronto_order';

    private $_helper;

    public function __construct()
    {
        /* @var $_helper Bronto_Order_Helper_Data */
        $this->_helper = Mage::helper(self::NOTICE_IDENTIFER);
    }

    /**
     * Verify that all requirements are met for this module
     * @param Varien_Event_Observer $observer
     * @return null
     * @access public
     */
    public function checkBrontoRequirements(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return;
        }

        // Verify Requirements
        if (!$this->_helper->varifyRequirements(self::NOTICE_IDENTIFER, array('soap', 'openssl'))) {
            return;
        }
    }

    /**
     * Process specified number of items for specified store
     * @param mixed $storeId    can be store object or id
     * @param int $limit      must be greater than 0
     * @return array
     * @access public
     */
    public function processOrdersForStore($storeId, $limit)
    {
        // Define default results
        $result = array('total' => 0, 'success' => 0, 'error' => 0);

        // If limit is false or 0, return
        if (!$limit) {
            $this->_helper->writeDebug('  Limit empty. Skipping...');
            return $result;
        }

        // Get Store object and ID
        $store = Mage::app()->getStore($storeId);
        $storeId = $store->getId();

        // Log that we have begun importing for this store
        $this->_helper->writeDebug("Starting Order Import process for store: {$store->getName()} ({$storeId})");

        // If module is not enabled for this store, log that fact and return
        if (!$store->getConfig(Bronto_Order_Helper_Data::XML_PATH_ENABLED)) {
            $this->_helper->writeDebug('  Module disabled for this store. Skipping...');
            return $result;
        }

        // Retrieve Store's configured API Token
        $token = $store->getConfig(Bronto_Common_Helper_Data::XML_PATH_API_TOKEN);

        /* @var $api Bronto_Common_Model_Api */
        $api = $this->_helper->getApi($token);

        /* @var $orderObject Bronto_Api_Order */
        $orderObject = $api->getOrderObject();

        // Retrieve order queue rows limited to current limit and filtered
        // Filter out imported, suppressed, other stores, and items without order ids
        $orderRows = Mage::getModel('bronto_order/queue')
            ->getCollection()
            ->addBrontoNotImportedFilter()
            ->addBrontoNotSuppressedFilter()
            ->addBrontoHasOrderFilter()
            ->orderByUpdatedAt()
            ->setPageSize($limit)
            ->addStoreFilter($storeId)
            ->getItems();

        // If we didn't get any order queue rows with this pull, log and return
        if (empty($orderRows)) {
            $this->_helper->writeVerboseDebug('  No Orders to process. Skipping...');
            return $result;
        }

        /* @var $productHelper Bronto_Common_Helper_Product */
        $productHelper = Mage::helper('bronto_common/product');
        $descriptionAttr = $store->getConfig(Bronto_Order_Helper_Data::XML_PATH_DESCRIPTION);
        $orderCache = array();

        // Cycle through each order queue row
        foreach ($orderRows as $orderRow/* @var $orderRow Bronto_Order_Model_Queue */) {
            $orderId = $orderRow->getOrderId();

            // Check if the order id is still attached to an order in magento
            if ($order = Mage::getModel('sales/order')->load($orderId)/* @var $order Mage_Sales_Model_Order */) {
                // Log that we are processing the current order
                $this->_helper->writeDebug("  Processing Order ID: {$orderId}");
                $orderCache[] = $orderId;

                /* @var $brontoOrder Bronto_Api_Order_Row */
                $brontoOrder = $orderObject->createRow();
                $brontoOrder->email = $order->getCustomerEmail();
                $brontoOrder->id = $order->getIncrementId();
                $brontoOrder->orderDate = date('c', strtotime($order->getCreatedAt()));

                // If there is a conversion tracking id attached to this order, add it to the row
                if ($tid = $orderRow->getBrontoTid()) {
                    $brontoOrder->tid = $tid;
                }
                $brontoOrderItems = array();

                // If the order has been cancelled, placed on hold, or closed we delete the row
                switch ($order->getState()) {
                    case Mage_Sales_Model_Order::STATE_CANCELED:
                    case Mage_Sales_Model_Order::STATE_HOLDED:
                    case Mage_Sales_Model_Order::STATE_CLOSED:
                        $brontoOrder->delete();
                        break;

                    default:
                        // Get visible items from order
                        $items = $order->getAllVisibleItems();

                        // Keep product order by using a new array
                        $fullItems = array();

                        // loop through the items. if it's a bundled item, 
                        // replace the parent item with the child items.
                        foreach ($items as $item) {
                            $itemProduct = Mage::getModel('catalog/product')->load($item->getProductId());

                            // Handle product based on product type
                            switch ($itemProduct->getTypeId()) {

                                // Bundled products need child items
                                case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                                    if (count($item->getChildrenItems()) > 0) {
                                        foreach ($item->getChildrenItems() as $child_item) {
                                            $fullItems[] = $child_item;
                                        }
                                    }
                                    break;

                                // Configurable products just need simple config item
                                case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                                    $childItems = $item->getChildrenItems();
                                    if (1 === count($childItems)) {
                                        $childItem = $childItems[0];

                                        // Collect options applicable to the configurable product
                                        $productAttributeOptions = $itemProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($itemProduct);

                                        // Build Selected Options Name
                                        $nameWithOptions = array();
                                        foreach ($productAttributeOptions as $productAttribute) {
                                            $itemValue = $productHelper->getProductAttribute($childItem->getProductId(), $productAttribute['attribute_code'], $storeId);
                                            $nameWithOptions[] = $productAttribute['label'] . ': ' . $itemValue;
                                        }

                                        // Set parent product name to include selected options
                                        $parentName = $item->getName() . ' [' . implode(', ', $nameWithOptions) . ']';
                                        $item->setName($parentName);
                                    }

                                    $fullItems[] = $item;
                                    break;

                                // Grouped products need parent and child items
                                case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                                    // This condition probably never gets hit, parent grouped items don't show in order
                                    $fullItems[] = $item;
                                    foreach ($item->getChildrenItems() as $child_item) {
                                        $fullItems[] = $child_item;
                                    }
                                    break;

                                // Anything else (namely simples) just get added to array
                                default:
                                    $fullItems[] = $item;
                                    break;
                            }
                        }

                        // Cycle through newly created array of products
                        foreach ($fullItems as $item/* @var $item Mage_Sales_Model_Order_Item */) {
                            // If product has a parent, get that parent product
                            $parent = false;
                            if ($item->getParentItem()) {
                                $parent = Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getParentItem()->getProductId());
                            }

                            /* @var $product Mage_Catalog_Model_Product */
                            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getProductId());

                            // If there is a parent product, use that to get category ids
                            if ($parent) {
                                $categoryIds = $parent->getCategoryIds();
                            } else {
                                $categoryIds = $product->getCategoryIds();
                            }

                            // Cycle through category ids to pull category details
                            $categories = array();
                            foreach ($categoryIds as $categoryId) {
                                /* @var $category Mage_Catalog_Model_Category */
                                $category = Mage::getModel('catalog/category')->load($categoryId);
                                $parent = $category->getParentCategory();
                                $categories[] = $parent->getUrlKey() ? $parent->getUrlKey() : $parent->formatUrlKey($parent->getName());
                                $categories[] = $category->getUrlKey() ? $category->getUrlKey() : $category->formatUrlKey($category->getName());
                            }

                            // Check to ensure there are no duplicate categories
                            $categories = array_unique($categories);

                            // Write orderItem
                            $brontoOrderItems[] = array(
                                'id' => $item->getId(),
                                'sku' => $item->getSku(),
                                'name' => $item->getName(),
                                'description' => $productHelper->getProductAttribute($item->getProductId(), $descriptionAttr),
                                'category' => implode(' ', $categories),
                                'image' => $this->_helper->getItemImg($item, $product, $storeId),
                                'url' => $this->_helper->getItemUrl($item, $product, $storeId),
                                'quantity' => (int)$item->getQtyOrdered(),
                                'price' => (float)$item->getPrice(),
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
                        $result = $this->_flushOrders($orderObject, $orderCache, $result);
                        $orderCache = array();
                    }
                } catch (Exception $e) {
                    $this->_helper->writeError($e);

                    // Mark import as *not* imported
                    $orderRow->setBrontoImported(null);
                    $orderRow->save();

                    // increment number of errors
                    $result['error']++;
                }

                // increment total number of items processed
                $result['total']++;
            }
        }

        // Final flush (for any we miss)
        $results = $this->_flushOrders($orderObject, $orderCache, $result);

        // Log results
        $this->_helper->writeDebug('  Success: ' . $results['success']);
        $this->_helper->writeDebug('  Error:   ' . $results['error']);
        $this->_helper->writeDebug('  Total:   ' . $results['total']);

        return $results;
    }

    /**
     * @param Bronto_Api_Order $orderObject
     * @param array $orderCache
     * @param array $result
     * @return array
     * @access protected
     */
    protected function _flushOrders($orderObject, $orderCache, $result)
    {
        // Get delivery results from order object
        $flushResult = $orderObject->flush();

        // Log Order import flush process starting
        $this->_helper->writeVerboseDebug('===== FLUSH =====', 'bronto_order_api.log');
        $this->_helper->writeVerboseDebug(var_export($orderObject->getApi()->getLastRequest(), true), 'bronto_order_api.log');
        $this->_helper->writeVerboseDebug(var_export($orderObject->getApi()->getLastResponse(), true), 'bronto_order_api.log');

        // Cycle through flush results and handle any errors that were returned
        foreach ($flushResult as $i => $flushResultRow) {
            $order = Mage::getModel('sales/order')->load($orderCache[$i]);

            if ($flushResultRow->hasError()) {
                // Get error code from result
                $errorCode = $flushResultRow->getErrorCode();

                // Get error message from result
                $errorMessage = $flushResultRow->getErrorMessage();

                // Check to see if this item exists in the order cache
                if (isset($orderCache[$i])) {
                    /* @var $order Mage_Sales_Model_Order */
                    $order = Mage::getModel('sales/order')->load($orderCache[$i]);

                    // If error code is 915, try to pull customer email address
                    if (915 == $errorCode) {
                        if ($customerEmail = $order->getCustomerEmail()) {
                            $errorMessage = "Invalid Email Address: `{$customerEmail}`";
                        } else {
                            $errorMessage = "Email Address is empty for this order";
                        }
                    }

                    // Append order id to message to assiste troubleshooting
                    $errorMessage .= " (Order #: {$order->getIncrementId()})";

                    // Reset Bronto Import status
                    Mage::getModel('bronto_order/queue')
                        ->getOrderRow($order->getId(), $order->getQuoteId(), $order->getStoreId())
                        ->setBrontoImported(null)
                        ->setBrontoSuppressed($errorMessage)
                        ->save();
                }

                // Log and Display error message
                $this->_helper->writeError("[{$errorCode}] {$errorMessage}");

                // Increment number of errors
                $result['error']++;
            } else {
                // Increment number of successes
                $result['success']++;
            }
        }

        return $result;
    }

    /**
     * Process Orders for all stores
     * @return array
     * @access public
     */
    public function processOrders()
    {
        // Set default result values
        $result = array(
            'total' => 0,
            'success' => 0,
            'error' => 0,
        );

        // Get limit value from config
        $limit = $this->_helper->getLimit();

        // Pull array of stores to cycle through
        $stores = Mage::app()->getStores(true);

        // Cycle through stores
        foreach ($stores as $_store) {
            // If limit is spent, don't process
            if ($limit <= 0) {
                continue;
            }

            // Process Orders for store and collect results
            $storeResult = $this->processOrdersForStore($_store, $limit);

            // Append results to totals
            $result['total'] += $storeResult['total'];
            $result['success'] += $storeResult['success'];
            $result['error'] += $storeResult['error'];

            // Decrement limit by resultant total
            $limit = $limit - $storeResult['total'];
        }

        return $result;
    }

}
