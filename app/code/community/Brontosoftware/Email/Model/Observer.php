<?php

class Brontosoftware_Email_Model_Observer extends Brontosoftware_Magento_Email_ExtensionAbstract
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        parent::__construct(
            Mage::getSingleton('brontosoftware_email/impl_trigger'),
            Mage::getSingleton('brontosoftware_connector/impl_core_orderStatuses'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_queue'),
            Mage::getSingleton('brontosoftware_connector/settings'),
            Mage::getSingleton('brontosoftware_email/settings'),
            Mage::getSingleton('brontosoftware_connector/impl_connector_platform'),
            new Brontosoftware_Magento_Email_Event_Source(),
            Mage::getSingleton('brontosoftware_connector/impl_connector_middleware'),
            Mage::getSingleton('brontosoftware_connector/impl_core_event'),
            Mage::getSingleton('brontosoftware_connector/impl_core_emulation'));
    }

    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::helper('brontosoftware_email')->__($message);
    }

    /**
     * @see parent
     */
    protected function _defaultTemplates()
    {
        $templates = array();
        foreach (Mage_Core_Model_Email_Template::getDefaultTemplates() as $templateId => $template) {
            if (preg_match('/^design_email/', $templateId)) {
                continue;
            }
            if (isset($template['@']) && isset($template['@']['module'])) {
                $module = $template['@']['module'];
            } else {
                $module = 'adminhtml';
            }
            $templates[] = array(
                'value' => $templateId,
                'label' => Mage::helper($module)->__($template['label']),
            );
        }
        return $templates;
    }

    /**
     * @see parent
     */
    protected function _customTemplates()
    {
        return Mage::getModel('core/email_template')->getCollection();
    }

    /**
     * @see parent
     */
    protected function _emailIdentities()
    {
        $identities = array();
        $identity = Mage::getModel('adminhtml/system_config_source_email_identity');
        foreach ($identity->toOptionArray() as $option) {
            $identities[] = array(
                'id' => $option['value'],
                'name' => $option['label']
            );
        }
        return $identities;
    }

    /**
     * @see parent
     */
    protected function _targetAudience()
    {
        $groups = array();
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->loadData()->toOptionArray();
        foreach ($customerGroups as $group) {
            $groups[] = array(
                'id' => $group['value'],
                'name' => $group['label']
            );
        }
        return $groups;
    }

    /**
     * @see parent
     */
    protected function _productCategories()
    {
        $options = array();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('path', array('neq' => '1'))
            ->addNameToResult();
        $tempTable = array();
        foreach ($categories as $category) {
            $options[] = array(
                'id' => $category->getId(),
                'name' => $category->getName()
            );
        }
        return $options;
    }
}
