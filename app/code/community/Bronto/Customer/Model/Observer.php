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
                
                /* Process Customer Attributes */
                $brontoContact = $this->processAttributes($brontoContact, $customer, $customerAttributes, $store, 'customer');
                
                /* Process Address Attributes */
                $primaryAddress = $customer->getPrimaryShippingAddress();
                if (!empty($primaryAddress)) {
                    $brontoContact = $this->processAttributes($brontoContact, $primaryAddress, $addressAttributes, $store, 'address');
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
    
    /**
     * Cycle through attributes and validate against Bronto Field type
     * @param Bronto_Api_Contact_Row $brontoContact
     * @param $source
     * @param $attributes
     * @param Mage_Core_Model_Store $store
     * @param string $type 'customer' or 'address'
     * @return Bronto_Api_Contact_Row
     */
    public function processAttributes(Bronto_Api_Contact_Row $brontoContact, $source, $attributes, Mage_Core_Model_Store $store, $type = 'customer')
    {
        // For each Customer attribute
        foreach ($attributes as $attribute) {
            $_attributeCode = $attribute->getAttributeCode();
            
            // Get Attribute Field
            switch ($type) {
                case 'address':
                    $_fieldName = Mage::helper('bronto_customer')->getAddressAttributeField($_attributeCode, $store);
                    break;
                default:
                    $_fieldName = Mage::helper('bronto_customer')->getCustomerAttributeField($_attributeCode, $store);
                    break;
            }
            
            // Get Customer Attribute Value
            $_attributeValue = $this->getReadableValue($attribute, $source->getData($_attributeCode));
                        
            // Skip un-mapped or empty attributes
            if (empty($_fieldName) || '_none_' == $_fieldName || !$_attributeValue || '' == $_attributeValue) {
                continue;
            }
            
            $brontoContact->setField($_fieldName, $_attributeValue);
        }
        
        return $brontoContact;
    }
    
    /**
     * Based on attribute type, pull the value or the label
     * @param type $attribute
     * @param type $value
     * @return type
     */
    public function getReadableValue($attribute, $value)
    {
        $_attributeType = $attribute->getFrontendInput();
        
        switch ($_attributeType) {
            case 'select':
            case 'boolean':
                return strtolower(Mage::helper('bronto_customer')->getAttributeAdminLabel($attribute, $value));
                break;
            case 'multiselect':
                $values = array();
                foreach ($value as $val) {
                    $values[] = strtolower(Mage::helper('bronto_customer')->getAttributeAdminLabel($attribute, $val));
                }
                return $values;
                break;
            case 'date':
                return Mage::getSingleton('core/date')->date('Y-m-d', $value);
                break;
            default:
                return $value;
                break;
        }
    }
    
    
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
        $fieldModel = Mage::getModel('bronto_common/system_config_source_field');
        $flushResult = $customerObject->flush();

        Mage::helper('bronto_customer')->writeVerboseDebug('===== FLUSH =====', 'bronto_customer_api.log');
        Mage::helper('bronto_customer')->writeVerboseDebug(var_export($customerObject->getApi()->getLastRequest(), true), 'bronto_customer_api.log');
        Mage::helper('bronto_customer')->writeVerboseDebug(var_export($customerObject->getApi()->getLastResponse(), true), 'bronto_customer_api.log');

        foreach ($flushResult as $i => $flushResultRow) {
            if ($flushResultRow->hasError()) {
                $errorCode    = $flushResultRow->getErrorCode();
                $errorMessage = $flushResultRow->getErrorMessage();
                
                // Catch Error and Replace Field ID with Field Name
                if ($errorCode === Bronto_Api_Field_Exception::INVALID_FIELD_VALUE || $errorCode === Bronto_Api_Field_Exception::DATA_TRUNCATION) {
                    if (preg_match("/'([^']+)'/", $errorMessage, $matches)) { // Grab field id if exists
                        $fieldObject  = $fieldModel->getFieldObjectById($matches[1]);
                        $errorMessage = str_replace($matches[1], $fieldObject->name, $errorMessage);
                    }
                }
                
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
    
    /**
     * Grab Config Data Object before save and handle the 'Create New...' value for 
     * fields that were generated dynamically
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    public function saveDynamicField(Varien_Event_Observer $observer)
    {
        if ($observer->getObject()->getSection() == 'bronto_customer') {
            $observer = $this->_handleAttributes($observer);
        }
        
        return $observer;
    }
    
    /**
     * Process customer and address attributes and save back to observer
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    protected function _handleAttributes(Varien_Event_Observer $observer)
    {
        $data = $observer->getObject()->getData();
        
        $attrFieldsCustomer = $this->_processAttributes($data['groups']['attributes']['fields']);
        $data['groups']['attributes']['fields'] = $attrFieldsCustomer;
            
        $attrFieldsAddress = $this->_processAttributes($data['groups']['address_attributes']['fields']);
        $data['groups']['address_attributes']['fields'] = $attrFieldsAddress;
            
        $observer->getObject()->setData($data);
        
        return $observer;
    }
    
    /**
     * Capture "Create New..." attributes, create field in Bronto, and save field id
     * @param  array $attributesFields
     * @return array
     */
    protected function _processAttributes(array $attributesFields)
    {
        foreach ($attributesFields as $fieldId => $field) {
            if (preg_match('/dynamic_new_/', $fieldId)) {
                $realfield = str_replace('dynamic_new_', '', $fieldId);
                $value     = $field['value'];

                /* @var $fieldObject Bronto_Api_Field */
                $fieldObject = Mage::helper('bronto_common')->getApi()->getFieldObject();

                $field        = $fieldObject->createRow();
                $field->name  = $fieldObject->normalize($value);
                $field->label = $value;
                $field->type  = Bronto_Api_Field::TYPE_TEXT;

                try {
                    $field->save();
                    $fieldObject->addToCache($field->name, $field);

                    $attributesFields[$realfield] = array('value' => $field->id);
                    unset($attributesFields[$fieldId]);
                } catch (Exception $e) {
                    Mage::helper('bronto_customer')->writeError("Unable to save new field: {$value}");
                }
            }
        }
        
        return $attributesFields;
    }
}
