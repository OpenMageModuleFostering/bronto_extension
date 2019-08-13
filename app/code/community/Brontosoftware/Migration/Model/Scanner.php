<?php

abstract class Brontosoftware_Migration_Model_Scanner
{
    protected $_scope = 'default';
    protected $_scopeId = '0';
    protected $_fieldsToLabel = array();

    /**
     * Sets the scope name for the scanner
     *
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        if ($scope != 'default') {
            $scope .= 's';
        }
        $this->_scope = $scope;
        return $this;
    }

    /**
     * Sets the scopeId for the scanner
     *
     * @param mixed $scopeId
     * @return $this
     */
    public function setScopeId($scopeId)
    {
        $this->_scopeId = $scopeId;
        return $this;
    }

    /**
     * Aggregates the settings stored in the DB at the built scope
     *
     * @param string $modulePath
     * @return array
     */
    public function getSettings($modulePath = null)
    {
        return $this->_afterConfig($this->_populateSettings($modulePath));
    }

    /**
     * Performs the actual scan into a collection
     *
     * @param string $modulePath
     * @return array
     */
    protected function _populateSettings($modulePath = null)
    {
        $settings = array();
        foreach ($this->_settings($modulePath) as $config) {
            list($module, $section, $name) = explode('/', $config->getPath());
            $fields = $this->_fieldToLabel($section);
            if (array_key_exists($name, $fields)) {
                $value = $this->_translateValue($section, $name, $config->getValue());
                if ($this->_filterNulls($value)) {
                    if (!array_key_exists($section, $settings)) {
                        $settings[$section] = array();
                    }
                    $settings[$section][$name] = array(
                        'name' => $fields[$name],
                        'value' => $value
                    );
                }
            }
        }
        return $settings;
    }

    /**
     * Null check
     *
     * @return mixed $value
     * @return boolean
     */
    protected function _filterNulls($value)
    {
        return !is_null($value);
    }

    /**
     * Gets the module config prefix
     *
     * @return string
     */
    protected abstract function _modulePath();

    /**
     * Gets a hash map for setting values to labels
     *
     * @param string $section
     * @return mixed
     */
    protected function _fieldToLabel($section)
    {
        return $this->_fieldsToLabel;
    }

    /**
     * Allows any value transformations
     *
     * @param string $section
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function _translateValue($section, $key, $value)
    {
        return $value;
    }

    /**
     * Add any additional information to the settings
     *
     * @param array $settings
     * @return array
     */
    protected function _afterConfig($settings)
    {
        return $settings;
    }

    /**
     * Gets a core config data collection from the scope and id
     *
     * @param string $modulePath
     * @return mixed
     */
    protected function _settings($modulePath = null)
    {
        return Mage::getSingleton('brontosoftware_connector/impl_core_config')
            ->getCollection()
            ->addFieldToFilter('path', array('like' => empty($modulePath) ? $this->_modulePath() : $modulePath))
            ->addFieldToFilter('scope', array('eq' => $this->_scope))
            ->addFieldToFilter('scope_id', array('eq' => $this->_scopeId));
    }
}
