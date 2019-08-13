<?php

/**
 * Verify Helper
 *
 * @category  Bronto
 * @package   Bronto_Verify
 * @author    Adam Daniels <adam.daniels@atlanticbt.com>
 * @copyright 2013 Adam Daniels
 * @license   http://www.atlanticbt.com/ Atlantic BT
 */
class Bronto_Verify_Helper_Data
    extends Bronto_Common_Helper_Data
{
    /**
     * Description for const
     */
    const XML_PATH_ROUNDTRIP_ROOT = 'bronto_verify/settings/';

    const XML_PATH_SOAP_CLIENT             = 'bronto_verify/soap_options/soap_client';
    const XML_PATH_SOAP_STREAM_CONTEXT     = 'bronto_verify/soap_options/stream_context';
    const XML_PATH_SOAP_RETRY_LIMIT        = 'bronto_verify/soap_options/retry_limit';
    const XML_PATH_SOAP_CONNECTION_TIMEOUT = 'bronto_verify/soap_options/connection_timeout';
    const XML_PATH_SOAP_TRACE              = 'bronto_verify/soap_options/trace';
    const XML_PATH_SOAP_EXCEPTIONS         = 'bronto_verify/soap_options/exceptions';
    const XML_PATH_WSDL_CACHE              = 'bronto_verify/soap_options/wsdl_cache';

    const DEFAULT_SOAP_CLIENT              = 'Bronto_SoapClient';

    /**
     * Module Human Readable Name
     */
    protected $_name = 'Bronto Advanced Configuration';

    /**
     * Check if module is enabled (Verify Module Always Enabled)
     *
     * @param string $scope
     * @param int    $scopeId
     *
     * @return bool
     */
    public function isEnabled($scope = 'default', $scopeId = 0)
    {
        true;
    }

    /**
     * Get Human Readable Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->__($this->_name);
    }

    /**
     * Get the full path from path ending
     *
     * @param string $pathend The setting to get the path for
     *
     * @return string
     * @access public
     */
    public function getPath($pathend)
    {
        return self::XML_PATH_ROUNDTRIP_ROOT . $pathend;
    }

    /**
     * Get SOAP Options
     *
     * @return array
     */
    public function getSoapOptions()
    {
        // Return Default Options
        return array(
            'soap_client'        => $this->getSoapClient(),
            'retry_limit'        => $this->getSoapRetryLimit(),
            'connection_timeout' => $this->getSoapConnectionTimeout(),
            'trace'              => $this->getSoapTrace(),
            'exceptions'         => $this->getSoapExceptions(),
            'cache_wsdl'         => $this->getSoapCacheWsdl(),
            'debug'              => $this->isDebugEnabled(),
        );
    }

    /**
     * Override the Bronto_SoapCLient class name
     *
     * @return string
     */
    public function getSoapClient()
    {
        $class = $this->getAdminScopedConfig(self::XML_PATH_SOAP_CLIENT);
        if (empty($class)) {
            $class = self::DEFAULT_SOAP_CLIENT;
        }
        if (
            $this->isStreamContextOverride() &&
            $class == self::DEFAULT_SOAP_CLIENT
        ) {
            $class = 'Bronto_Common_Model_SoapClient';
        }
        return $class;
    }

    /**
     * Override the default Soap client with the stream context override
     *
     * @return bool
     */
    public function isStreamContextOverride()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_SOAP_STREAM_CONTEXT);
    }

    /**
     * Get Config Value for SOAP Retry Limit
     *
     * @return int
     */
    public function getSoapRetryLimit()
    {
        return (int)$this->getAdminScopedConfig(self::XML_PATH_SOAP_RETRY_LIMIT);
    }

    /**
     * Get Config Value for SOAP Connection Timeout
     *
     * @return int
     */
    public function getSoapConnectionTimeout()
    {
        return (int)$this->getAdminScopedConfig(self::XML_PATH_SOAP_CONNECTION_TIMEOUT);
    }

    /**
     * Get Config Value for SOAP Trace
     *
     * @return bool
     */
    public function getSoapTrace()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_SOAP_TRACE) == '1';
    }

    /**
     * Get Config Value for SOAP Exceptions
     *
     * @return bool
     */
    public function getSoapExceptions()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_SOAP_EXCEPTIONS) == '1';
    }

    public function getSoapCacheWsdl()
    {
        $cacheWsdl = $this->getAdminScopedConfig(self::XML_PATH_WSDL_CACHE);
        switch ($cacheWsdl) {
            case 'WSDL_CACHE_NONE':
                return WSDL_CACHE_NONE;
            case 'WSDL_CACHE_DISK':
                return WSDL_CACHE_DISK;
            case 'WSDL_CACHE_MEMORY':
                return WSDL_CACHE_MEMORY;
            case 'WSDL_CACHE_BOTH':
            default:
                return WSDL_CACHE_BOTH;
        }
    }

    /**
     * Set the value of a setting
     *
     * @param string     $path The setting path to set the value for
     * @param string     $value
     * @param string     $scope
     * @param int|string $scopeId
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function setStatus($path, $value, $scope = null, $scopeId = null)
    {
        $scope   = (in_array($scope, array('default', 'websites', 'stores'))) ? $scope : 'default';
        $scopeId = (is_int($scopeId)) ? $scopeId : 0;

        return Mage::getSingleton('core/config')
            ->saveConfig($path, $value, $scope, $scopeId);
    }
}
