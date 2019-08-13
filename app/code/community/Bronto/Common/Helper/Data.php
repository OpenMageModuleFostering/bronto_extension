<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    const XML_PATH_GLOBAL_SETTINGS = 'bronto/settings/';
    const XML_PATH_API_TOKEN       = 'bronto/settings/api_token';
    const XML_PATH_DEBUG           = 'bronto/settings/debug';
    const XML_PATH_VERBOSE         = 'bronto/settings/verbose';
    const XML_PATH_TEST            = 'bronto/settings/test';
    const XML_PATH_NOTICES         = 'bronto/settings/notices';
    const XML_PATH_ENABLED         = 'bronto/settings/enabled';

    const XML_PATH_IMAGE_TYPE   = 'bronto/format/image_type';
    const XML_PATH_IMAGE_WIDTH  = 'bronto/format/image_width';
    const XML_PATH_IMAGE_HEIGHT = 'bronto/format/image_height';
    const XML_PATH_USE_SYMBOL   = 'bronto/format/use_symbol';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getAdminScopedConfig(self::XML_PATH_ENABLED);
    }

    /*
     * Get Text to display in notice when enabling module
     *
     * @return string
     */
    public function getModuleEnabledText()
    {
        return $this->__('If you have changed your API token, please ensure you reconfigure all available options.');
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function disableModule($scope = 'default', $scopeId = 0)
    {
        return $this->_disableModule(self::XML_PATH_ENABLED, $scope, $scopeId);
    }

    /**
     * Determine if email can be sent through bronto
     *
     * @param Mage_Core_Model_Email_Template $template
     *
     * @return boolean
     */
    public function canSendBronto(Mage_Core_Model_Email_Template $template, $storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return string
     */
    public function getProductImageUrl($product)
    {
        return (string) Mage::helper('catalog/image')
                        ->init($product, $this->getImageType($product->getStoreId()))
                        ->resize(
                $this->getImageWidth($product->getStoreId()),
                $this->getImageHeight($product->getStoreId())
            );
    }

    /**
     * @return string
     */
    public function getImageType($storeId = null)
    {
        return $this->getAdminScopedConfig(self::XML_PATH_IMAGE_TYPE, $storeId);
    }

    /**
     * @return int|null
     */
    public function getImageWidth($storeId = null)
    {
        $width = (int) $this->getAdminScopedConfig(self::XML_PATH_IMAGE_WIDTH, $storeId);

        return empty($width) ? NULL : abs($width);
    }

    /**
     * @return int|null
     */
    public function getImageHeight($storeId = null)
    {
        $height = (int) $this->getAdminScopedConfig(self::XML_PATH_IMAGE_HEIGHT, $storeId);

        return empty($height) ? NULL : abs($height);
    }

    /**
     * @return bool
     */
    public function useCurrenySymbol($storeId = null)
    {
        return (bool) $this->getAdminScopedConfig(self::XML_PATH_USE_SYMBOL, $storeId);
    }

    /**
     * @param string $path
     * @param string $scope
     * @param int    $scopeId
     *
     * @return bool
     */
    protected function _disableModule($path, $scope = 'default', $scopeId = 0)
    {
        $config = Mage::getModel('core/config');
        $config->saveConfig($path, 0, $scope, $scopeId);

        if (!$this->isVersionMatch(Mage::getVersionInfo(), 1, array(4, 5, 9, 10))) {
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
     * Determine if module is active
     * @return boolean
     */
    public function isModuleActive()
    {
        // If module is not enabled, return false
        if (!$this->isEnabled()) {
            return FALSE;
        }

        // If module is missing token, return false
        if (!$this->getApiToken()) {
            return FALSE;
        }

        // If requirements are not met, return false
        if (!$this->verifyRequirements($this->_getModuleName())) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Does this helper have custom config?
     *
     * @return boolean
     */
    public function hasCustomConfig()
    {
        return FALSE;
    }

    /**
     * @deprecated since version 1.6.7
     * @see        verifyRequirements
     */
    public function varifyRequirements($module, $required = array())
    {
        return $this->verifyRequirements($module, $required);
    }

    /**
     * Verify that all required PHP extensions are loaded
     *
     * @param string $module
     * @param array  $required
     *
     * @return boolean
     */
    public function verifyRequirements($module, $required = array())
    {
        // Check for required PHP extensions
        $verified        = TRUE;
        $missing         = array();
        $defaultRequired = array('soap', 'openssl');
        $required        = array_merge($required, $defaultRequired);
        $module          = strtolower($module);

        /*
         * Run through PHP extensions to see if they are loaded
         * if no, add them to the list of missing and set verified = false flag
         */
        foreach ($required as $extName) {
            try {
                if (!extension_loaded($extName)) {
                    $missing[] = $extName;
                    $verified  = FALSE;
                }
            }
            catch (Exception $e) {
                $missing[] = $extName;
                $verified  = FALSE;
            }
        }

        // If not verified, create a message telling the user what they are missing
        if (!$verified) {
            // If module is enabled, disable it
            if (Mage::helper($module)->isEnabled()) {
                Mage::helper($module)->disableModule();
            }
            // Create message informing of missing extensions
            $message = Mage::getSingleton('core/message')->error(
                $this->__(
                    sprintf(
                        'The module "' .
                        $module .
                        '" has been automatically disabled due to missing PHP extensions: %s',
                        implode(',', $missing)
                    )
                )
            );
            $message->setIdentifier($module);
            Mage::getSingleton('adminhtml/session')->addMessage($message);

            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $token
     * @param int    $store
     * @param int    $websiteId
     *
     * @return Bronto_Common_Model_Api
     */
    public function getApi($token = NULL, $store = NULL, $websiteId = NULL)
    {
        if (empty($token)) {
            $token = $this->getApiToken($store, $websiteId);
        }

        return Bronto_Common_Model_Api::getInstance($token);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getApiToken($store = NULL, $websiteId = NULL)
    {
        $token = $this->getAdminScopedConfig(self::XML_PATH_API_TOKEN, $store, $websiteId);

        if (!$token || empty($token)) {
            return FALSE;
        }

        return $token;
    }

    /**
     * Determine if API token is valid
     *
     * @param string $token
     * @param int    $store
     * @param int    $websiteId
     *
     * @return boolean
     */
    public function validApiToken($token = NULL, $store = NULL, $websiteId = NULL)
    {
        if (empty($token)) {
            $token = $this->getApiToken($store, $websiteId);
        }

        if (strlen($token) < 36) {
            return FALSE;
        }
        try {
            $api = new Bronto_Api($token, array('debug' => TRUE));
            $api->login();
        }
        catch (Exception $e) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Check all API tokens are valid
     * @return boolean
     */
    public function validApiTokens($identifier = 'bronto_common')
    {
        $valid = TRUE;
        if (!$this->validApiToken()) {
            $message = Mage::getSingleton('core/message')->error(
                $this->__('The Bronto API Token you have entered for Default Configuration appears to be invalid.')
            );
            $message->setIdentifier($identifier);
            Mage::getSingleton('adminhtml/session')->addMessage($message);
            $valid = FALSE;
        }
        foreach (Mage::app()->getWebsites() as $website) {
            if (!$this->validApiToken(NULL, NULL, $website->getId())) {
                $message = Mage::getSingleton('core/message')->error(
                    $this->__(
                        sprintf(
                            'The Bronto API Token you have entered for website "%s" appears to be invalid.',
                            $website->getName()
                        )
                    )
                );
                $message->setIdentifier($identifier);
                Mage::getSingleton('adminhtml/session')->addMessage($message);
                $valid = FALSE;
            }
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) > 0) {
                    foreach ($stores as $store) {
                        if (!$this->validApiToken(NULL, $store->getId(), $website->getId())) {
                            $message = Mage::getSingleton('core/message')->error(
                                $this->__(
                                    sprintf(
                                        'The Bronto API Token you have entered for store "%s" on website "%s" appears to be invalid.',
                                        $store->getName(),
                                        $website->getName()
                                    )
                                )
                            );
                            $message->setIdentifier($identifier);
                            Mage::getSingleton('adminhtml/session')->addMessage($message);
                            $valid = FALSE;
                        }
                    }
                }
            }
        }

        return $valid;
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     */
    public function isModuleInstalled($moduleName = NULL)
    {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        if ($moduleName === NULL) {
            $moduleName = $this->_getModuleName();
        }

        return isset($modules[$moduleName]);
    }

    /**
     * @param string $moduleName
     *
     * @return string
     */
    public function getModuleVersion($moduleName = NULL)
    {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        if ($moduleName === NULL) {
            $moduleName = $this->_getModuleName();
        }

        return isset($modules[$moduleName]) ? (string) $modules[$moduleName]->version : NULL;
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        if (!$this->getApiToken()) {
            return FALSE;
        }

        return (bool) $this->getAdminScopedConfig(self::XML_PATH_DEBUG);
    }

    /**
     * @return bool
     */
    public function isVerboseEnabled()
    {
        if (!$this->isDebugEnabled()) {
            return FALSE;
        }

        return (bool) $this->getAdminScopedConfig(self::XML_PATH_VERBOSE);
    }

    /**
     * @return bool
     */
    public function isTestModeEnabled()
    {
        if (!$this->getApiToken()) {
            return FALSE;
        }

        return (bool) $this->getAdminScopedConfig(self::XML_PATH_TEST);
    }

    /**
     * @return bool
     */
    public function isNoticesEnabled()
    {
        if (!$this->getApiToken()) {
            return FALSE;
        }

        return (bool) $this->getAdminScopedConfig(self::XML_PATH_NOTICES);
    }

    /**
     * @param string      $message
     * @param string|null $file
     *
     * @return bool|void
     */
    public function writeDebug($message, $file = NULL, $verbose = FALSE)
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
     *
     * @return bool|void
     */
    public function writeVerboseDebug($message, $file = NULL)
    {
        if ($this->isVerboseEnabled()) {
            return $this->writeDebug($message, $file, TRUE);
        }
    }

    /**
     * @param string      $message
     * @param string|null $file
     *
     * @return bool|void
     */
    public function writeInfo($message, $file = NULL)
    {
        if ($this->isNoticesEnabled()) {
            if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                /* @var $message Mage_Core_Model_Message_Notice */
                $message = Mage::getSingleton('core/message')->notice("[Bronto] {$message}");
                Mage::getSingleton('adminhtml/session')->addMessage($message);
            }
            else {
                Mage::getSingleton('core/session')->addNotice("[Bronto] {$message}");
            }
        }

        return $this->writeLog($message, $file, Zend_Log::INFO);
    }

    /**
     * @param Exception|string $message
     * @param string|null      $file
     *
     * @return bool|void
     */
    public function writeError($message, $file = NULL)
    {
        if (is_object($message) && $message instanceOf Exception) {
            $message = $message->getMessage();
        }
        if ($this->isNoticesEnabled()) {
            if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                /* @var $message Mage_Core_Model_Message_Error */
                $message = Mage::getSingleton('core/message')->error("[Bronto] {$message}");
                Mage::getSingleton('adminhtml/session')->addMessage($message);
            }
            else {
                Mage::getSingleton('core/session')->addError("[Bronto] {$message}");
            }
        }

        return $this->writeLog($message, $file, Zend_Log::ERR);
    }

    /**
     * @param string      $message
     * @param string|null $file
     * @param int         $level
     *
     * @return bool|void
     */
    public function writeLog($message, $file = NULL, $level = Zend_Log::DEBUG)
    {
        if (empty($file)) {
            $file = strtolower($this->_getModuleName()) . '.log';
        }
        if (!is_string($message)) {
            if (method_exists($message, '__toString')) {
                $message = $message->__toString();
            }
            else {
                return FALSE;
            }
        }

        return Mage::log($message, $level, $this->_stampFile($file), TRUE);
    }

    /**
     * Add Date Stamp to log file name
     *
     * @param type $filename
     *
     * @return type
     */
    protected function _stampFile($filename, $withTime = TRUE)
    {
        // Ensure var/log/bronto exists
        $logDir = Mage::getBaseDir('var') . DS . 'log' . DS . 'bronto';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, TRUE);
        }

        // If time stamp requested, append
        if ($withTime) {
            $stamp    = date('Ymd', time());
            $filename = str_replace('.', ".{$stamp}.", $filename);
        }

        // replace bronto_ with bronto/ to place in folder
        return str_replace('bronto_', 'bronto' . DS, $filename);
    }

    /**
     * Get list of active custom modules
     * @return array
     */
    public function getInstalledModules()
    {
        $moduleList = array();
        $modules    = Mage::getConfig()->getNode('modules')->children();

        foreach ($modules as $name => $module) {
            if (strpos($name, 'Mage_') === FALSE && strpos($name, 'Enterprise_') === FALSE &&
                $module->active == 'true'
            ) {
                $moduleList[] = $name . ' [v' . $module->version . ' codePool: ' . $module->codePool . ']';
            }
        }

        return $moduleList;
    }

    /**
     * Get array of current scope parameters
     * @return array
     */
    public function getScopeParams()
    {
        // Get Request Object
        $request = Mage::app()->getRequest();

        // Define Scope Params
        $scopeParams = array(
            'scope'      => 'default',
            'default'    => 0,
            'default_id' => 0,
            'store'      => $request->getParam('store', FALSE),
            'store_id'   => 0,
            'website'    => $request->getParam('website', FALSE),
            'website_id' => 0,
            'group'      => $request->getParam('group', FALSE),
            'group_id'   => 0,
        );

        // Update Scope based on what has been set
        if ($scopeParams['store']) {
            $store = Mage::app()->getStore($scopeParams['store']);
            if ($store->getId()) {
                $scopeParams['store_id'] = $store->getId();
            }
            $scopeParams['scope'] = 'store';
        }
        elseif ($scopeParams['website']) {
            $website = Mage::app()->getWebsite($scopeParams['website']);
            if ($website->getId()) {
                $scopeParams['website_id'] = $website->getId();
            }
            $scopeParams['scope'] = 'website';
        }
        elseif ($scopeParams['group']) {
            $group = Mage::app()->getGroup($scopeParams['group']);
            if ($group->getId()) {
                $scopeParams['group_id'] = $group->getId();
            }
            $scopeParams['scope'] = 'group';
        }

        // Return array of Scope Params
        return $scopeParams;
    }

    /**
     * Get Url with scope data included
     *
     * @param string $url
     *
     * @return string
     */
    public function getScopeUrl($url, $scopeParams = array())
    {
        $curScopeParams = $this->getScopeParams();
        $curScope       = array(
            'scope'                  => $curScopeParams['scope'],
            $curScopeParams['scope'] => $curScopeParams[$curScopeParams['scope']],
        );
        $scopeParams    = array_merge($scopeParams, $curScope);

        return Mage::helper('adminhtml')->getUrl($url, $scopeParams);
    }

    /**
     * @param string $path
     * @param mixed  $store
     * @param int    $websiteId
     *
     * @return mixed
     */
    public function getAdminScopedConfig($path, $store = NULL, $websiteId = NULL)
    {
        if (!is_null($store)) {
            return Mage::getStoreConfig($path, $store);
        }
        elseif (!is_null($websiteId)) {
            $website = Mage::app()->getWebsite($websiteId);

            return $website->getConfig($path);
        }

        $scopeParams = $this->getScopeParams();
        $source      = FALSE;

        switch ($scopeParams['scope']) {
            case 'store':
                $source = Mage::app()->getStore($scopeParams['store']);
                break;
            case 'website':
                $source = Mage::app()->getWebsite($scopeParams['website']);
                break;
            case 'group':
                $source = Mage::app()->getGroup($scopeParams['group'])->getWebsite();
                break;
            default:
                return Mage::getStoreConfig($path);
                break;
        }

        if ($source) {
            return $source->getConfig($path);
        }

        return Mage::getStoreConfig($path);
    }

    /**
     * Get Array of Store Ids based on current store/website/group
     * @return boolean|array
     */
    public function getStoreIds()
    {
        $scopeParams = $this->getScopeParams();

        switch ($scopeParams['scope']) {
            case 'store':
                $source   = Mage::app()->getStore($scopeParams['store']);
                $storeIds = $source->getId();
                break;
            case 'website':
                $source   = Mage::app()->getWebsite($scopeParams['website']);
                $storeIds = $source->getStoreIds();
                break;
            case 'group':
                $source   = Mage::app()->getGroup($scopeParams['group'])->getWebsite();
                $storeIds = $source->getStoreIds();
                break;
            default:
                $storeIds = array_keys(Mage::app()->getStores(TRUE));
                break;
        }

        return $storeIds;
    }

    /**
     * Is this the Enterprise edition?
     *
     * @return boolean
     */
    public function isEnterpriseEdition()
    {
        return ('Enterprise' == $this->getEdition());
    }

    /**
     * Get Edition from version Info
     *
     * @param  array|boolean $versionInfo
     *
     * @return string|boolean
     */
    public function getEdition($versionInfo = FALSE)
    {
        // Ensure we have version info
        if (!$versionInfo || !is_array($versionInfo)) {
            if (method_exists('Mage', 'getEdition')) {
                return Mage::getEdition();
            }
            $versionInfo = Mage::getVersionInfo();
        }

        // Get Edition from version
        if (array_key_exists('major', $versionInfo) && array_key_exists('minor', $versionInfo)) {
            $major = $versionInfo['major'];
            $minor = $versionInfo['minor'];

            if (1 == $major) {
                if ($minor < 9) {
                    return 'Community';
                }
                else if ($minor >= 9 && $minor < 11) {
                    return 'Professional';
                }
                else if ($minor >= 11) {
                    return 'Enterprise';
                }
            }
        }

        return FALSE;
    }

    /**
     * Takes major and minor version info and determines if current magento install matches
     *
     * @param array            $versionInfo
     * @param int|string|array $major
     * @param int|string|array $minor
     * @param int|string|array $revision (Optional)
     * @param int|string|array $patch    (Optional)
     * @param string           $edition  (Optional)      'CE'|'Community'|'PE'|'Professional'|'EE'|'Enterprise'
     *
     * @return boolean
     */
    public function isVersionMatch()
    {
        /**
         * Get arguments passed to function
         *
         * [0] = Magento Version Array (Required)
         * [1] = Compare Major Version (Optional)
         * [2] = Compare Minor Version (Optional)
         * [3] = Compare Revision Number (Optional)
         * [4] = Compare Patch Number (Optional)
         * [5] = Compare Edition (Optional)
         */
        $parts = $this->_mapVersionParts(func_get_args());

        // At least version info and one other
        if (!array_key_exists('versionInfo', $parts) || count($parts) < 2) {
            return FALSE;
        }

        // Get Magento Version from passed arguments
        $mageVersion            = $parts['versionInfo'];
        $mageVersion['edition'] = $this->getEdition($mageVersion);
        unset($parts['versionInfo']);

        // Cycle through the elements of the magento version
        foreach ($mageVersion as $index => $mValue) {
            // If the compare value doesn't exist for this index, continue
            if (!isset($parts[$index])) {
                continue;
            }

            // Get compare value
            $value = $parts[$index];
            // Ensure Value is an array
            if (!is_array($value)) {
                $value = array($value);
            }

            // Cycle through compare value array to compare against 
            // current Magento version element
            $internalMatch = FALSE;
            foreach ($value as $option) {
                $operator = '==';
                $compare  = $option;

                // If the current compare value is an array, 
                // get the operator and value provided
                if (is_array($option)) {
                    list ($operator, $compare) = $option;
                }

                if ($index == 'edition') {
                    // handle posibility of initials being used
                    switch (strtoupper($compare)) {
                        case 'EE':
                            $compare = 'Enterprise';
                            break;
                        case 'CE':
                            $compare = 'Community';
                            break;
                        case 'PE':
                            $compare = 'Professional';
                            break;
                        default:
                            break;
                    }

                    // If response from getEdition matches compare edition
                    $internalMatch = ($mValue == $compare);
                }
                else {
                    // Use version_compare to compare the Magento version to the
                    // Current compare version using the provided operator
                    $internalMatch = version_compare($mValue, $compare, $operator);
                }

                if ($internalMatch) {
                    break;
                }
            }

            // If the internal Match flag hasn't been set to true, 
            // there is no match
            if (!$internalMatch) {
                return FALSE;
            }
        }

        // If we haven't returned false yet, that means there is a match
        return TRUE;
    }

    /**
     * Maps parts array to expected array
     *
     * @param  array $parts
     *
     * @return array
     */
    private function _mapVersionParts($parts)
    {
        // Parts must be array
        if (!is_array($parts)) {
            return FALSE;
        }

        // Generate index map values
        $mapKeys = array(
            'versionInfo' => 0,
            'major'       => 1,
            'minor'       => 2,
            'revision'    => 3,
            'patch'       => 4,
            'edition'     => 5,
        );

        // Placeholder array
        $versionParts = array();

        // Cycle Through and map values as needed
        foreach ($mapKeys as $map => $index) {
            if (array_key_exists($index, $parts) && !is_null($parts[$index])) {
                $versionParts[$map] = $parts[$index];
            }
        }

        // Return Mapped Array
        return $versionParts;
    }
}
