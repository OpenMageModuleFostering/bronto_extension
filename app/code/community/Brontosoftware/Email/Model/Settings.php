<?php

class Brontosoftware_Email_Model_Settings extends Brontosoftware_Magento_Email_SettingsAbstract
{
    /**
     * @override for DI
     */
    public function __construct() {
        $config = Mage::getModel('brontosoftware_connector/impl_core_config');
        parent::__construct(
            Mage::getSingleton('brontosoftware_connector/settings'),
            $config,
            Mage::getSingleton('brontosoftware_connector/impl_core_scoped'),
            $config,
            Mage::getSingleton('brontosoftware_connector/impl_core_emulation'),
            Mage::getSingleton('brontosoftware_connector/impl_core_store'),
            Mage::getSingleton('brontosoftware_connector/impl_core_event'),
            Mage::getSingleton('brontosoftware_connector/impl_core_logger'));
    }

    /**
     * @see parent
     */
    public function getModelTuple($model)
    {
        $modelType = 'template';
        if ($model instanceof Mage_Sales_Model_Order) {
            $modelType = 'order';
        } else if ($model instanceof Mage_Sales_Model_Quote) {
            $modelType = 'cart';
        } else if ($model instanceof Mage_Wishlist_Model_Wishlist) {
            $modelType = 'wishlist';
        } else if ($model instanceof Mage_Sales_Model_Order_Item) {
            $modelType = 'order_item';
        }
        return array($modelType, $model->getId());
    }

    /**
     * @see parent
     */
    public function getTriggerModel(Brontosoftware_Magento_Email_TriggerInterface $trigger)
    {
        return $this->loadModel($trigger->getModelType(), $trigger->getModelId());
    }

    /**
     * @see parent
     */
    public function loadModel($modelType, $modelId)
    {
        $modelClass = 'core/email_template';
        switch ($modelType) {
        case 'order':
            $modelClass = 'sales/order';
            break;
        case 'cart':
            $modelClass = 'sales/quote';
            break;
        case 'wishlist':
            $modelClass = 'wishlist/wishlist';
            break;
        case 'order_item':
            $modelClass = 'sales/order_item';
            break;
        }
        $model = Mage::getModel($modelClass)->load($modelId);
        if (!$model->getId()) {
            return null;
        }
        return $model;
    }

    /**
     * @see parent
     */
    public function getTemplateFilter()
    {
        return Mage::getModel('brontosoftware_email/template_filter')
            ->setUseSessionInUrl(false)
            ->setUseAbsoluteLinks(true)
            ->setPlainTemplateMode(false);
    }

    /**
     * @see parent
     */
    public function getTemplate($templateId, $options = array())
    {
        $template = Mage::getModel('core/email_template');
        if (is_numeric($templateId)) {
            $template->load($templateId);
        } else {
            $template->loadDefault($templateId);
            if (!empty($options)) {
                $template->setDesignConfig($options);
            }
            $defaults = Mage_Core_Model_Email_Template::getDefaultTemplates();
            $row = $defaults[$template->getId()];
            $template->setTemplateCode($row['label']);
        }
        return $template;
    }

    /**
     * @see parent
     */
    protected function _applyFilterFunctions($template, $filter)
    {
        if (method_exists($filter, 'getInlineCssFile')) {
            $filter->setCssInliner($template);
        }
        return $filter
            ->setTemplateProcessor(array($template, 'getTemplateByConfigPath'))
            ->setIncludeProcessor(array($template, 'getInclude'));
    }

    /**
     * @see parent
     */
    protected function _getEmailVariableMethod()
    {
        return '_' . parent::_getEmailVariableMethod();
    }
}
