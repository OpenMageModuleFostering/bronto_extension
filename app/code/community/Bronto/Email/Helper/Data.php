<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Helper_Data extends Bronto_Common_Helper_Data implements Bronto_Common_Helper_DataInterface
{
    const XML_PATH_ENABLED = 'bronto_email/settings/enabled';
    const XML_PATH_USE_BRONTO = 'bronto_email/settings/use_bronto';
    const XML_PATH_LOG_ENABLED = 'bronto_email/settings/log_enabled';
    const XML_PATH_LOG_FIELDS_ENABLED = 'bronto_email/settings/log_fields_enabled';

    /**
     * @param string $path
     * @return bool
     */
    public function disableModule($scope = 'default', $scopeId = 0)
    {
        return $this->_disableModule(self::XML_PATH_ENABLED, $scope, $scopeId);
    }

    /**
     * Xml path to email template nodes
     */
    const XML_PATH_TEMPLATE_EMAIL = '//sections/*/groups/*/fields/*[source_model="adminhtml/system_config_source_email_template"]';

    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        return 'Bronto_Email';
    }

    /**
     * Determine if any stores have module enabled
     *
     * @return bool
     */
    public function isEnabledForAny()
    {
        $stores = Mage::app()->getStores();
        if (is_array($stores) && count($stores) >= 1) {
            foreach ($stores as $store) {
                if ($this->isEnabled($store->getId())) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string|int $store
     * @param string|int $website
     * @return bool
     */
    public function isEnabled($store = null, $website = null)
    {
        if (!$this->getApiToken($store, $website)) {
            return false;
        }

        return (bool)$this->getAdminScopedConfig(self::XML_PATH_ENABLED, $store, $website);
    }

    /*
     * Get Text to display in notice when enabling module
     *
     * @return string
     */
    public function getModuleEnabledText()
    {
        $message = parent::getModuleEnabledText();
        $scopeData = $this->getScopeParams();
        if ($scopeData['scope'] != 'default') {
            $message = $this->__(
                'If the API token being used for this configuration scope is different from that of the Default Config scope, ' .
                'you should un-check the `Use Website` or `Use Default` for ALL options in the <em>Assign Templates</em> group on this page ' .
                'and select the desired templates.'
            );
        }
        return $message;
    }

    /**
     * Get Config setting for sending through bronto
     *
     * @param string|int $store
     * @param string|int $website
     * @return boolean
     */
    public function canUseBronto($store = null, $website = null)
    {
        if (!$this->getApiToken($store, $website)) {
            return false;
        }

        return (bool)$this->getAdminScopedConfig(self::XML_PATH_USE_BRONTO, $store, $website);
    }

    /**
     * Sets the "Send through Bronto" option for any config scope
     *
     * @param boolean $brontoSend
     * @param int $storeId
     * @param int $websiteId
     * @return Bronto_Email_Helper_Data
     */
    public function setUseBronto($brontoSend, $storeId = null, $websiteId = null)
    {
        if (!is_null($storeId)) {
            $scope = 'stores';
            $scopeId = $storeId;
        } else if (!is_null($websiteId)) {
            $scope = 'websites';
            $scopeId = $websiteId;
        } else {
            $scope = 'default';
            $scopeId = '0';
        }

        $config = Mage::getModel('core/config');
        $config->saveConfig(self::XML_PATH_USE_BRONTO, $brontoSend ? '1' : '0', $scope, $scopeId);
        return $this;
    }

    /**
     * Determine if email can be sent through bronto
     *
     * @param Mage_Core_Model_Email_Template $template
     * @return boolean
     */
    public function canSendBronto(Mage_Core_Model_Email_Template $template, $storeId = null)
    {
        if ($this->isEnabled($storeId) && $this->canUseBronto($storeId) && $template->getTemplateSendType() != 'magento') {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isLogEnabled()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_LOG_ENABLED);
    }

    /**
     * @return bool
     */
    public function isLogFieldsEnabled()
    {
        return (bool)$this->getAdminScopedConfig(self::XML_PATH_LOG_FIELDS_ENABLED);
    }

    /**
     * @see parent
     * @return bool
     */
    public function hasCustomConfig() {
        return true;
    }

    /**
     * Gets any saved emails, and reports it
     *
     * @return array
     */
    public function getCustomConfig() {
        $emails = array();
        $templates = Mage::getModel('bronto_email/template')->getCollection();

        if ($this->isVersionMatch(Mage::getVersionInfo(), 1, array(4, 5, 9, 10))) {
            $templateTable = Mage::getSingleton('core/resource')->getTableName('bronto_email/template');
            $brontoTable = Mage::getSingleton('core/resource')->getTableName('bronto_email/message');
            $templates->getSelect()->joinLeft(
                $brontoTable,
                "`{$templateTable}`.`template_id` = `{$brontoTable}`.`core_template_id`"
            );
        }

        $templates->addFieldToFilter('bronto_message_id', array('notnull' => true));

        foreach ($templates as $template) {
            $emails[] = array(
                'template_id' => $template->getTemplateId(),
                'template_code' => $template->getTemplateCode(),
                'bronto_message_id' => $template->getBrontoMessageId(),
                'bronto_message_name' => $template->getBrontoMessageName(),
                'send_type' => $template->getTemplateSendType(),
            );
        }

        $settings = array();
        foreach ($this->getTemplatePaths() as $configPath) {
            $data = Mage::getStoreConfig($configPath);
            if (str_replace('/', '_', $configPath) == $data) {
                $data = 'Default';
            }
            $settings[$configPath] = $data;
        }

        return array(
            'templates' => $emails,
            'settings' => $settings,
        );
    }

    /**
     * Get array of all template config paths
     * @return array
     */
    public function getTemplatePaths()
    {
        $templatePaths = array();

        $configSections = Mage::getSingleton('adminhtml/config')->getSections();

        // look for node entries in all system.xml that use source_model=adminhtml/system_config_source_email_template
        // they are will be templates, what we try find
        $sysCfgNodes = $configSections->xpath(self::XML_PATH_TEMPLATE_EMAIL);
        if (!is_array($sysCfgNodes)) {
            return array();
        }

        foreach ($sysCfgNodes as $fieldNode) {

            $groupNode = $fieldNode->getParent()->getParent();
            $sectionNode = $groupNode->getParent()->getParent();

            // create email template path in system.xml
            $sectionName = $sectionNode->getName();
            $groupName = $groupNode->getName();
            $fieldName = $fieldNode->getName();

            $templatePaths[] = implode('/', array($sectionName, $groupName, $fieldName));
        }

        return $templatePaths;
    }
}
