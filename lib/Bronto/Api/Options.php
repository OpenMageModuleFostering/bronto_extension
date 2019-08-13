<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Api/Options.php
 */


/**
 * An options container class for the Api client
 *
 * @author Philip Cali <philip.cali@bronto.com>
 */
class Bronto_Api_Options extends Bronto_Object
{
    public static $defaultOptions = array(
        'soapClass' => 'SoapClient',
        'wsdl' => 'https://api.bronto.com/v4?wsdl',
        'error' => 'Bronto_Api_Strategy_Standard',
        'retries' => 5,
        'backOff' => 5,
        'soapOptions' => array(
            'trace' => true,
            'exceptions' => true,
            'encoding' => 'UTF-8',
            'connection_timeout' => 30,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'cache_wsdl' => WSDL_CACHE_NONE
        )
    );

    private $_observer;
    private $_retryer;
    private $_error;
    private $_soapClient;

    /**
     * Create a options with a data array that overrides settings
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($this->_mergeData(self::$defaultOptions, $data));
        $this->_init();
    }

    /**
     * Fix for array_merge_recursive
     */
    protected function _mergeData($returnArray, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists($key, $returnArray)) {
                    $value = $this->_mergeData($returnArray[$key], $value);
                }
            }
            $returnArray[$key] = $value;
        }
        return $returnArray;
    }

    /**
     * @see parent
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        $result = parent::__call($name, $args);
        if (preg_match('/(?:SoapClient|Observer|Retryer|Error)$/', $name)) {
            $this->_init();
        }
        return $result;
    }

    /**
     * Gets an option type that represents the API observer
     *
     * @return Bronto_Functional_Option[Bronto_Api_Observer]
     */
    public function safeObserver()
    {
        if (is_null($this->_observer)) {
            return new Bronto_Functional_None();
        }
        return new Bronto_Functional_Some($this->_observer);
    }

    /**
     * Gets an option type that represents the API retryer
     *
     * @return Bronto_Functional_Option[Bronto_Api_Retryer]
     */
    public function safeRetryer()
    {
        if (is_null($this->_retryer)) {
            return new Bronto_Functional_None();
        }
        return new Bronto_Functional_Some($this->_retryer);
    }

    /**
     * Gets an option type that represents the error strategy
     *
     * @return Bronto_Functional_Option[Bronto_Api_Strategy_Error]
     */
    public function safeError()
    {
        if (is_null($this->_error)) {
            return new Bronto_Functional_None();
        }
        return new Bronto_Functional_Some($this->_error);
    }

    /**
     * Internal init to set implementation from data details
     * for the retryer, observer, and error strategy
     *
     * @return void
     */
    protected function _init()
    {
        $classChecks = array(
            'observer' => 'Bronto_Api_Observer',
            'retryer' => 'Bronto_Api_Retryer',
            'error' => 'Bronto_Api_Strategy_Error'
        );
        foreach ($classChecks as $field => $className) {
            if (array_key_exists($field, $this->_data)) {
                $value = $this->_data[$field];
        // Note: This snippet was generated with legacy conversion
        if (is_string($value) && !class_exists($value, false) && !array_key_exists($value, Bronto_ImportManager::$_fileCache)) {
            $dir = preg_replace('|' . str_replace("_", "/", "Bronto_Api") . '$|', '', dirname(__FILE__));
            $file = $dir . str_replace("_", "/", $value) . '.php';
            if (file_exists($file)) {
                require_once $file;
                Bronto_ImportManager::$_fileCache[$value] = true;
            } else {
                Bronto_ImportManager::$_fileCache[$value] = false;
            }
        }
        // End Conversion Snippet
                if (is_string($value) && (class_exists($value, false) || Bronto_ImportManager::$_fileCache[$value])) {
                    $value = new $value();
                }
                if (is_a($value, $className)) {
                    $this->{"_$field"} = $value;
                } else {
                    unset($this->{"_$field"});
                }
            }
        }
    }
}
