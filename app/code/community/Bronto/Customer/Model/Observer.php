<?php

/**
 * @package     Bronto\Customer
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.3.5
 */
class Bronto_Customer_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * @param  mixed $storeId
     * @return array
     */
    public function processCustomersForStore($storeId = null)
    {
        if (is_object($storeId)) {
            $store   = $storeId;
            $storeId = $store->getId();
        } else {
            $store   = Mage::app()->getStore($storeId);
            $storeId = $store->getId();
        }

        $result = array('total' => 0, 'success' => 0, 'error' => 0);
        Mage::helper('bronto_customer')->writeDebug("Starting Customer Import process for store: {$store->getName()} ({$storeId})");

        if (!$store->getConfig(Bronto_Customer_Helper_Data::XML_PATH_ENABLED)) {
            Mage::helper('bronto_customer')->writeDebug('  Module disabled for this store. Skipping...');
            return false;
        }

        // Retrieve Store's configured API Token
        $token = $store->getConfig(Bronto_Common_Helper_Data::XML_PATH_API_TOKEN);

        /* @var $api Bronto_Common_Model_Api */
        $api = Mage::helper('bronto_customer')->getApi($token);

        /* @var $contactObject Bronto_Api_Contact */
        $contactObject = $api->getContactObject();

        $limit = $store->getConfig(Bronto_Customer_Helper_Data::XML_PATH_LIMIT);
        if (!$limit) {
            Mage::helper('bronto_customer')->writeDebug('  Limit empty. Skipping...');
            return false;
        }

        $customerIds = Mage::getModel('bronto_customer/resource_customer_collection')
            ->addStoreFilter($storeId)
            ->addBrontoNotImportedFilter()
            ->orderByUpdatedAt()
            ->getAllIds($limit);

        if (empty($customerIds)) {
            Mage::helper('bronto_customer')->writeVerboseDebug('  No Customers to process. Skipping...');
            return $result;
        }

        $customerAttributes = Mage::getModel('customer/entity_attribute_collection')->addVisibleFilter();
        $addressAttributes  = Mage::getModel('customer/entity_address_attribute_collection')->addVisibleFilter();
        $customerCache      = array();

        // For each Customer...
        foreach ($customerIds as $customerId) {
            if ($customer = Mage::getModel('customer/customer')->load($customerId) /* @var $customer Mage_Customer_Model_Customer */) {
                Mage::helper('bronto_customer')->writeDebug("  Processing Customer ID: {$customerId}");
                $customerCache[] = $customerId;

                /* @var $brontoContact Bronto_Api_Contact_Row */
                $brontoContact = $contactObject->createRow();
                $brontoContact->email = $customer->getEmail();

                // For each Customer attribute
                foreach ($customerAttributes as $attributeId => $attribute) {
                    $_attributeCode = $_attribute->getAttributeCode();
                    $_fieldName     = Mage::helper('bronto_customer')->getCustomerAttributeField($_attributeCode, $store);
                    if (!empty($_fieldName)) {
                        $brontoContact->setField($_fieldName, $customer->getAttribute($_attributeCode));
                    }
                }

                // For each Customer Address attribute
                $primaryAddress = $customer->getPrimaryShippingAddress();
                if (!empty($primaryAddress)) {
                    foreach ($addressAttributes as $attributeId => $attribute) {
                        $_attributeCode = $_attribute->getAttributeCode();
                        $_fieldName     = Mage::helper('bronto_customer')->getAddressAttributeField($_attributeCode, $store);
                        if (!empty($_fieldName)) {
                            $brontoContact->setField($_fieldName, $primaryAddress->getAttribute($_attributeCode));
                        }
                    }
                }

                $brontoContact->persist();

                try {
                    // Mark Customer as imported
                    $customer->setBrontoImported(Mage::getSingleton('core/date')->gmtDate());
                    $customer->save();

                    // Flush every 10 Customers
                    if ($result['total'] % 100 === 0) {
                        $result        = $this->flushCustomers($customerObject, $customerCache, $result);
                        $customerCache = array();
                    }
                } catch (Exception $e) {
                    Mage::helper('bronto_customer')->writeError($e);

                    // Mark Customer as *not* imported
                    $customer->setBrontoImported(null);
                    $customer->save();

                    $result['error']++;
                }

                $result['total']++;
            }
        }

        // Final flush (for any we miss)
        $result = $this->flushCustomers($customerObject, $customerCache, $result);

        Mage::helper('bronto_customer')->writeDebug('  Success: ' . $result['success']);
        Mage::helper('bronto_customer')->writeDebug('  Error:   ' . $result['error']);
        Mage::helper('bronto_customer')->writeDebug('  Total:   ' . $result['total']);

        return $result;
    }

    /**
     * @param  Bronto_Api_Customer $customerObject
     * @param  array $customerCache
     * @param  array $result
     * @return array
     */
    public function flushCustomers($customerObject, $customerCache, $result)
    {
        $flushResult = $customerObject->flush();

        Mage::helper('bronto_customer')->writeVerboseDebug('===== FLUSH =====', 'bronto_customer_api.log');
        Mage::helper('bronto_customer')->writeVerboseDebug(var_export($customerObject->getApi()->getLastRequest(), true), 'bronto_customer_api.log');
        Mage::helper('bronto_customer')->writeVerboseDebug(var_export($customerObject->getApi()->getLastResponse(), true), 'bronto_customer_api.log');

        foreach ($flushResult as $i => $flushResultRow) {
            if ($flushResultRow->hasError()) {
                $errorCode    = $flushResultRow->getErrorCode();
                $errorMessage = $flushResultRow->getErrorMessage();
                if (isset($customerCache[$i])) {
                    // Reset Bronto Import status
                    $customer = Mage::getModel('sales/customer')->load($customerCache[$i]);
                    $customer->setBrontoImported(null);
                    $customer->save();
                    Mage::helper('bronto_customer')->writeError("[{$errorCode}] {$errorMessage} ({$customer->getIncrementId})");
                } else {
                    Mage::helper('bronto_customer')->writeError("[{$errorCode}] {$errorMessage}");
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
    public function processCustomers()
    {
        $result = array(
            'total'   => 0,
            'success' => 0,
            'error'   => 0,
        );

        $stores = Mage::app()->getStores();
        foreach ($stores as $_storeId => $_store) {
            $storeResult = $this->processCustomersForStore($_store);
            $result['total']   += $storeResult['total'];
            $result['success'] += $storeResult['success'];
            $result['error']   += $storeResult['error'];
        }

        return $result;
    }
}
