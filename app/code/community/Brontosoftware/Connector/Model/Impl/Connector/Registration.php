<?php

class Brontosoftware_Connector_Model_Impl_Connector_Registration implements Brontosoftware_Magento_Connector_RegistrationManagerInterface
{
    protected $_caches = array();

    /**
     * @see parent
     */
    public function getByScope($scope, $scopeId)
    {
        $cacheKey = "{$scope}:{$scopeId}";
        if (!array_key_exists($cacheKey, $this->_caches)) {
            $registration = Mage::getModel('brontosoftware_connector/registration')
                ->loadByScope($scope, $scopeId);
            if ($registration->getId()) {
                $this->_caches[$cacheKey] = $registration;
            } else {
                $this->_caches[$cacheKey] = null;
            }
        }
        return $this->_caches[$cacheKey];
    }

    /**
     * @see parent
     */
    public function getAll()
    {
        return Mage::getModel('brontosoftware_connector/registration')
            ->getCollection()
            ->filterByActive();
    }
}
