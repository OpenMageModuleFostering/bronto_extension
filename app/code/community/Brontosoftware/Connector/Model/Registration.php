<?php

class Brontosoftware_Connector_Model_Registration extends Mage_Core_Model_Abstract implements Brontosoftware_Magento_Connector_RegistrationInterface
{
    /**
     * @see parent
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('brontosoftware_connector/registration');
    }

    /**
     * Loads the registration by scope and scope id
     *
     * @param string $scope
     * @param mixed $scopeId
     * @return $this
     */
    public function loadByScope($scope, $scopeId)
    {
        $this->_getResource()->loadByScope($this, $scope, $scopeId);
        return $this;
    }

    /**
     * @see parent
     */
    public function setScopeHash($hash)
    {
        list($scopeName, $scopeId, $scopeCode) = explode('.', $hash);
        return $this
            ->setScope($scopeName)
            ->setScopeId($scopeId)
            ->setScopeCode($scopeCode);
    }

    /**
     * @see parent
     */
    public function getScopeHash($includeCode = false)
    {
        $things = array($this->getScope(), $this->getScopeId());
        if ($includeCode) {
            $things[] = $this->getScopeCode();
        }
        return implode('.', $things);
    }

    /**
     * @see parent
     */
    public function getEnvironment()
    {
        return $this->getData(self::ENVIRONMENT);
    }

    /**
     * @see parent
     */
    public function getScopeId()
    {
        return $this->getData(self::SCOPE_ID);
    }

    /**
     * @see parent
     */
    public function getScope()
    {
        return $this->getData(self::SCOPE_NAME);
    }

    /**
     * @see parent
     */
    public function getScopeCode()
    {
        return $this->getData(self::SCOPE_CODE);
    }

    /**
     * @see parent
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @see parent
     */
    public function getConnectorKey()
    {
        return $this->getData(self::CONNECTOR_KEY);
    }

    /**
     * @see parent
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @see parent
     */
    public function getUsername()
    {
        return $this->getData(self::USERNAME);
    }

    /**
     * @see parent
     */
    public function getPassword()
    {
        return $this->getData(self::PASSWORD);
    }

    /**
     * @see parent
     */
    public function getIsProtected()
    {
        return $this->getData(self::IS_PROTECTED);
    }

    /**
     * @see parent
     */
    public function getPlatformSuffix()
    {
        return '';
    }
}
