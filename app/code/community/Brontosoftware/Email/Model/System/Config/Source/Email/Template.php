<?php

class Brontosoftware_Email_Model_System_Config_Source_Email_Template extends Mage_Adminhtml_Model_System_Config_Source_Email_Template
{
    /**
     * @see parent
     */
    public function toOptionArray()
    {
        $request = Mage::app()->getRequest();
        $configPath = $this->getPath();
        $scopeType = 'default';
        $scopeId = null;
        if ($request->has('website')) {
            $scopeType = 'website';
            $scopeId = $request->getParam('website');
        } elseif ($request->has('store')) {
            $scopeType = 'store';
            $scopeId = $request->getParam('store');
        }
        $templateId = Mage::getModel('brontosoftware_connector/impl_core_scoped')->getValue($configPath, $scopeType, $scopeId);
        if (empty($templateId)) {
            $templateId = str_replace('/', '_', $configPath);
        }
        $mappingId = Mage::getModel('brontosoftware_email/settings')->getLookup($templateId, $scopeType, $scopeId, true);
        if ($mappingId) {
            $label = Mage::helper('adminhtml')->__('-- Configured within Bronto Connector --');
            return array(
                array( 'value' => $templateId, 'label' => $label )
            );
        } else {
            return parent::toOptionArray();
        }
    }
}
