<?php

class Brontosoftware_Connector_Model_Impl_Core_QuoteManagement implements Brontosoftware_Magento_Core_Sales_QuoteManagementInterface
{
    protected $_customerRepo;

    /**
     * Override for DI
     */
    public function __construct()
    {
        $this->_customerRepo = Mage::getSingleton('brontosoftware_connector/impl_core_customer');
    }

    /**
     * @see parent
     */
    public function assignCustomer($quoteId, $customerId, $storeId)
    {
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        if ($customer = $this->_customerRepo->getById($customerId)) {
            if ($quote->getStoreId() == $storeId) {
                $quote->assignCustomer($customer);
                return $quote;
            }
        }
        throw new RuntimeException("Unable to find customer {$customerId}");
    }

    /**
     * @see parent
     */
    public function getCartForCustomer($customerId)
    {
        $quote = Mage::getModel('sales/quote')->loadByCustomer($customerId);
        if ($quote->getId()) {
            return $quote;
        }
        throw new RuntimeException("Unable to find an active quote for {$customerId}");
    }

    /**
     * @see parent
     */
    public function getById($quoteId)
    {
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        if ($quote->getId()) {
            return $quote;
        }
        return null;
    }
}
