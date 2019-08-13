<?php

/**
 * @package     Bronto\Customer
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.0.2
 */
class Bronto_Customer_Model_Observer extends Mage_Core_Model_Abstract
{
    //  {{{ processCustomersForStore()

    /**
     * @param  mixed $storeId
     *
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
        
        // Get all customers in queue who haven't been imported into bronto
        $customerRows = Mage::getModel('bronto_customer/queue')
            ->getCollection()
            ->addBrontoNotImportedFilter()
            ->orderByUpdatedAt()
            ->setPageSize($limit)
            ->addStoreFilter($storeId)
            ->getItems();

        if (empty($customerRows)) {
            Mage::helper('bronto_customer')->writeVerboseDebug('  No Customers to process. Skipping...');
            return $result;
        }

        $customerAttributes = Mage::getModel('customer/entity_attribute_collection');
        $addressAttributes  = Mage::getModel('customer/entity_address_attribute_collection')->addVisibleFilter();
        $customerCache      = array();

        // For each Customer...
        foreach ($customerRows as $customerRow) {
            $customerId = $customerRow->getCustomerId();
            if ($customer = Mage::getModel('customer/customer')->load($customerId) /* @var $customer Mage_Customer_Model_Customer */) {
                Mage::helper('bronto_customer')->writeDebug("  Processing Customer ID: {$customerId}");
                $customerCache[] = array('customerId' => $customerId, 'storeId' => $storeId);
                
                /* @var $brontoContact Bronto_Api_Contact_Row */
                $brontoContact = $contactObject->createRow();
                $brontoContact->email = $customer->getEmail();
                
                // For each Customer attribute
                foreach ($customerAttributes as $attributeId => $attribute) {   
                    $_attributeCode = $attribute->getAttributeCode();
                    $_fieldName     = Mage::helper('bronto_customer')->getCustomerAttributeField($_attributeCode, $store);
                    
                    if (!empty($_fieldName) && $_fieldName != '_none_') {
                        switch ($_attributeCode) {
                            case 'gender':
                                $attrValue = Mage::helper('bronto_customer')->getAttributeAdminLabel($attribute, $customer->getData($_attributeCode));
                                break;
                            case 'dob':
                                if ($dob = $customer->getData($_attributeCode)) {
                                    $attrValue = Mage::getSingleton('core/date')->date('Y-m-d', $dob);
                                }
                                break;
                            default: 
                                $attrValue = $customer->getData($_attributeCode);
                                break;
                        }
                        
                        if ($attrValue != '') {
                            $brontoContact->setField($_fieldName, $attrValue);
                        }
                    }
                }

                // For each Customer Address attribute
                $primaryAddress = $customer->getPrimaryShippingAddress();
                if (!empty($primaryAddress)) {
                    foreach ($addressAttributes as $attributeId => $attribute) {
                        $_attributeCode = $attribute->getAttributeCode();
                        $_fieldName     = Mage::helper('bronto_customer')->getAddressAttributeField($_attributeCode, $store);
                        
                        if (!empty($_fieldName) && $_fieldName != '_none_') {
                            $brontoContact->setField($_fieldName, $primaryAddress->getData($_attributeCode));
                        }
                    }
                }
                
                $brontoContact->persist();

                try {
                    // Mark Customer as imported
                    $customerRow->setBrontoImported(Mage::getSingleton('core/date')->gmtDate());
                    $customerRow->save();
                    
                    // Flush every 10 Customers
                    if ($result['total'] % 100 === 0) {
                        $result        = $this->flushCustomers($contactObject, $customerCache, $result);
                        $customerCache = array();
                    }
                } catch (Exception $e) {
                    Mage::helper('bronto_customer')->writeError($e);

                    // Mark Customer as *not* imported
                    $customerRow->setBrontoImported(null);
                    $customerRow->save();

                    $result['error']++;
                }

                $result['total']++;
            }
        }

        // Final flush (for any we miss)
        $results = $this->flushCustomers($contactObject, $customerCache, $result);

        Mage::helper('bronto_customer')->writeDebug('  Success: ' . $results['success']);
        Mage::helper('bronto_customer')->writeDebug('  Error:   ' . $results['error']);
        Mage::helper('bronto_customer')->writeDebug('  Total:   ' . $results['total']);

        return $results;
    }

    //  }}}
    //  {{{ flushCustomers()

    /**
     * @param  Bronto_Api_Customer $customerObject
     * @param  array $customerCache
     * @param  array $result
     *
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
                    // Get Customer Object
                    $customer = Mage::getModel('customer/customer')->load($customerCache[$i]['customerId']);
                    
                    // Reset Bronto Import status
                    $customerRow = Mage::getModel('bronto_customer/queue')
                        ->getCustomerRow($customerCache[$i]['customerId'], $customerCache[$i]['storeId'])
                        ->setBrontoImported(null)
                        ->save();
                    
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

    //  }}}
    //  {{{ processCustomers()

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

        $stores = Mage::app()->getStores(true);
        foreach ($stores as $_store) {
            $storeResult = $this->processCustomersForStore($_store);
            $result['total']   += $storeResult['total'];
            $result['success'] += $storeResult['success'];
            $result['error']   += $storeResult['error'];
        }

        return $result;
    }

    //  }}}
    //  {{{ markCustomerForReimport()

    /**
     * @param Varien_Event_Observer $observer
     */
    public function markCustomerForReimport(Varien_Event_Observer $observer)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getCustomer();
        
        /* @var $contactQueue Bronto_Customer_Model_Queue */
        $customerRow = Mage::getModel('bronto_customer/queue')
                ->getCustomerRow($customer->getId(), Mage::app()->getStore()->getId())
                ->setCreatedAt($customer->getCreatedAt())
                ->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate())
                ->setBrontoImported(null)
                ->save();
    }

    //  }}}
}
