<?php

class Brontosoftware_Connector_Model_Impl_Core_Customer implements Brontosoftware_Magento_Core_Customer_CacheInterface
{
    private $_customerRepo = array();

    /**
     * @see parent
     */
    public function getById($customerId)
    {
        if (!array_key_exists($customerId, $this->_customerRepo)) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customer->getId()) {
                $this->_customerRepo[$customerId] = $customer;
            } else {
               return null;
            }
        }
        return $this->_customerRepo[$customerId];
    }

    /**
     * @see parent
     */
    public function getByEmail($email)
    {
        if (!array_key_exists($email, $this->_customerRepo)) {
            $customer = Mage::getModel('customer/customer')->loadByEmail($email);
            if ($customer->getId()) {
                $this->_customerRepo[$customer->getId()] = $customer;
            } else {
                return null;
            }
        }
    }
}
