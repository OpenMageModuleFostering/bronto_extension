<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_GLOBAL_SETTINGS = 'bronto/settings/';
    const XML_PATH_API_TOKEN       = 'bronto/settings/api_token';
    const XML_PATH_DEBUG           = 'bronto/settings/debug';
    const XML_PATH_VERBOSE         = 'bronto/settings/verbose';
    const XML_PATH_TEST            = 'bronto/settings/test';
    const XML_PATH_NOTICES         = 'bronto/settings/notices';
    const XML_PATH_ENABLED         = 'bronto/settings/enabled';

    /**
     * @param string $path
     * @param mixed  $store
     * @param int    $websiteId
     * @return mixed
     */
    public function getAdminScopedConfig($path, $store = null, $websiteId = null)
    {
        if (!is_null($store)) {
            return Mage::getStoreConfig($path, $store);
        } elseif (!is_null($websiteId)) {
            $website = Mage::app()->getWebsite($websiteId);
            return $website->getConfig($path);
        }

        $action = Mage::app()->getFrontController()->getAction();
        if ($action instanceOf Mage_Adminhtml_System_ConfigController) {
            if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
                $store = Mage::app()->getStore($storeCode);
                return $store->getConfig($path);
            } elseif ($websiteCode = Mage::app()->getRequest()->getParam('website')){
                $website = Mage::app()->getWebsite($websiteCode);
                return $website->getConfig($path);
            } else if ($groupCode = Mage::app()->getRequest()->getParam('group')){
                $website = Mage::app()->getGroup($groupCode)->getWebsite();
                return $website->getConfig($path);
            }
        }

        return Mage::getStoreConfig($path);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * Verify that all required PHP extensions are loaded
     *
     * @param string  $module
     * @param array   $required
     * @return boolean
     */
    public function varifyRequirements($module, $required = array())
    {
        // Check for required PHP extensions
        $verified = true;
        $missing  = array();
        $defaultRequired = array('soap', 'openssl');
        $required = array_merge($required, $defaultRequired);

        /*
         * Run through PHP extensions to see if they are loaded
         * if no, add them to the list of missing and set verified = false flag
         */
        foreach ($required as $extName) {
            if (!extension_loaded($extName)) {
                $missing[] = $extName;
                $verified  = false;
            }
        }

        // If not verified, create a message telling the user what they are missing
        if (!$verified) {
            // If module is enabled, disable it
            if ($this->isEnabled()) {
                Mage::helper($module)->disableModule();
            }
            // Create message informing of missing extensions
            $message = Mage::getSingleton('core/message')->error(
                Mage::helper('bronto_common')->__(
                    sprintf(
                        'The module "'.$module.'" has been automatically disabled due to missing PHP extensions: %s',
                        implode(',', $missing)
                    )
                )
            );
            $message->setIdentifier($module);
            Mage::getSingleton('adminhtml/session')->addMessage($message);
            return false;
        }

        return true;
    }

    /**
     * @param string $token
     * @param int    $store
     * @param int    $websiteId
     * @return Bronto_Common_Model_Api
     */
    public function getApi($token = null, $store = null, $websiteId = null)
    {
        if (empty($token)) {
            $token = $this->getApiToken($store, $websiteId);
        }

        return Bronto_Common_Model_Api::getInstance($token);
    }

    /**
     * Determine if API token is valid
     *
     * @param string  $token
     * @param int     $store
     * @param int     $websiteId
     * @return boolean
     */
    public function validApiToken($token = null, $store = null, $websiteId = null)
    {
        if (empty($token)) {
            $token = $this->getApiToken($store, $websiteId);
        }

        if (strlen($token) < 36) {
            return false;
        }
        try {
            $api = new Bronto_Api($token, array('debug' => true));
            $api->login();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Check all API tokens are valid
     * @return boolean
     */
    public function validApiTokens($identifier = 'bronto_common')
    {
        $valid = true;
        if (!$this->validApiToken()) {
            $message = Mage::getSingleton('core/message')->error(
                Mage::helper('bronto_common')->__('The Bronto API Token you have entered for Default Configuration appears to be invalid.')
            );
            $message->setIdentifier($identifier);
            Mage::getSingleton('adminhtml/session')->addMessage($message);
            $valid = false;
        }
        foreach (Mage::app()->getWebsites() as $website) {
            if (!$this->validApiToken(null, null, $website->getId())) {
                $message = Mage::getSingleton('core/message')->error(
                    Mage::helper('bronto_common')->__(sprintf('The Bronto API Token you have entered for website "%s" appears to be invalid.', $website->getName()))
                );
                $message->setIdentifier($identifier);
                Mage::getSingleton('adminhtml/session')->addMessage($message);
                $valid = false;
            }
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) > 0) {
                    foreach ($stores as $store) {
                        if (!$this->validApiToken(null, $store->getId(), $website->getId())) {
                            $message = Mage::getSingleton('core/message')->error(
                                Mage::helper('bronto_common')->__(sprintf('The Bronto API Token you have entered for store "%s" on website "%s" appears to be invalid.', $store->getName(), $website->getName()))
                            );
                            $message->setIdentifier($identifier);
                            Mage::getSingleton('adminhtml/session')->addMessage($message);
                            $valid = false;
                        }
                    }
                }
            }
        }

        return $valid;
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isModuleInstalled($moduleName = null)
    {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }

        return isset($modules[$moduleName]);
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleVersion($moduleName = null)
    {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }

        return isset($modules[$moduleName]) ? (string) $modules[$moduleName]->version : null;
    }

    /**
     * @param string $store
     * @return string
     */
    public function getApiToken($store = null, $websiteId = null)
    {
        $token = $this->getAdminScopedConfig(self::XML_PATH_API_TOKEN, $store, $websiteId);

        if (!$token || empty($token)) {
            return false;
        }

        return $token;
    }

    /**
     * @param string $store
     * @return Bronto_Api_ApiToken_Row
     */
    public function getApiTokenRow($store = null)
    {
        if (!($token = $this->getApiToken($store))) {
            return false;
        }

        if ($api = $this->getApi($token)) {
            if (!$api->isAuthenticated()) {
                return false;
            }
        } else {
            return false;
        }

        $apiTokenObject = $api->getApiTokenObject();
        $apiToken       = $apiTokenObject->createRow();
        $apiToken->id   = $token;
        try {
            $apiToken->read();
        } catch (Exception $e) {
            $this->writeError($e);
            return false;
        }

        return $apiToken;
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        if (!$this->getApiToken()){
            return false;
        }

        return (bool) Mage::getStoreConfig(self::XML_PATH_DEBUG);
    }

    /**
     * @return bool
     */
    public function isVerboseEnabled()
    {
        if (!$this->isDebugEnabled()) {
            return false;
        }

        return (bool) Mage::getStoreConfig(self::XML_PATH_VERBOSE);
    }

    /**
     * @return bool
     */
    public function isTestModeEnabled()
    {
        if (!$this->getApiToken()){
            return false;
        }

        return (bool) Mage::getStoreConfig(self::XML_PATH_TEST);
    }

    /**
     * @return bool
     */
    public function isNoticesEnabled()
    {
        if (!$this->getApiToken()){
            return false;
        }

        return (bool) Mage::getStoreConfig(self::XML_PATH_NOTICES);
    }

    /**
     * @param string $path
     * @param string $scope
     * @param int    $scopeId
     * @return bool
     */
    protected function _disableModule($path, $scope = 'default', $scopeId = 0)
    {
        $config = Mage::getModel('core/config');
        $config->saveConfig($path, 0, $scope, $scopeId);

        $version = Mage::getVersionInfo();
        if (   1 == $version['major']
            && (9 != $version['minor'] && 10 != $version['minor'])
        ) {
            //  Get the Module alias from the path
            //  $path = bronto_email/settings/api_token
            //  $module = bronto_email
            list($module) = explode('/', $path);
            //  we have to physically insert the enabled path into the
            //  core_config_data table of the DB w/ a value of 0, or the module
            //  could inherit from its parent and not actually get disabled.
            //  b/c the state of the checkbox is determined by whether or not
            //  a value is set in the core_config_data table.
            $configData = Mage::getModel('core/config_data');
            $configData->setScope($scope)
                       ->setScopeId($scopeId)
                       ->setPath("$module/settings/enabled")
                       ->setValue(0)
                       ->save();
        }

        return $this;
    }

    /**
     * @param string      $message
     * @param string|null $file
     * @return bool|void
     */
    public function writeDebug($message, $file = null, $verbose = false)
    {
        if ($verbose && !$this->isVerboseEnabled()) {
            return;
        }

        if ($this->isDebugEnabled()) {
            return $this->writeLog($message, $file, Zend_Log::DEBUG);
        }
    }

    /**
     * @param string      $message
     * @param string|null $file
     * @return bool|void
     */
    public function writeVerboseDebug($message, $file = null)
    {
        if ($this->isVerboseEnabled()) {
            return $this->writeDebug($message, $file, true);
        }
    }

    /**
     * @param string      $message
     * @param string|null $file
     * @return bool|void
     */
    public function writeInfo($message, $file = null)
    {
        if ($this->isNoticesEnabled()) {
            if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                /* @var $message Mage_Core_Model_Message_Notice */
                $message = Mage::getSingleton('core/message')->notice("[Bronto] {$message}");
                Mage::getSingleton('adminhtml/session')->addMessage($message);
            } else {
                Mage::getSingleton('core/session')->addNotice("[Bronto] {$message}");
            }
        }
        return $this->writeLog($message, $file, Zend_Log::INFO);
    }

    /**
     * @param Exception|string $message
     * @param string|null      $file
     * @return bool|void
     */
    public function writeError($message, $file = null)
    {
        if (is_object($message) && $message instanceOf Exception) {
            $message = $message->getMessage();
        }
        if ($this->isNoticesEnabled()) {
            if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                /* @var $message Mage_Core_Model_Message_Error */
                $message = Mage::getSingleton('core/message')->error("[Bronto] {$message}");
                Mage::getSingleton('adminhtml/session')->addMessage($message);
            } else {
                Mage::getSingleton('core/session')->addError("[Bronto] {$message}");
            }
        }
        return $this->writeLog($message, $file, Zend_Log::ERR);
    }

    /**
     * @param string      $message
     * @param string|null $file
     * @param int         $level
     * @return bool|void
     */
    public function writeLog($message, $file = null, $level = Zend_Log::DEBUG)
    {
        if (empty($file)) {
            $file = strtolower($this->_getModuleName()) . '.log';
        }
        if (!is_string($message)) {
            if (method_exists($message, '__toString')) {
                $message = $message->__toString();
            } else {
                return false;
            }
        }
        return Mage::log($message, $level, $file, true);
    }
}
