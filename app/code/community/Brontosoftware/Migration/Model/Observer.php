<?php

class Brontosoftware_Migration_Model_Observer
{
    /**
     * Adds the appropriate script to the scheduled task
     */
    public function advancedAdditional($observer)
    {
        $helper = Mage::helper('brontosoftware_migration');
        if ($helper->isModuleOutputEnabled('Bronto_Common')) {
            $observer->getEndpoint()->addOptionToScript('event', 'jobName', array(
                'id' => 'previewMigration',
                'name' => $helper->__('Preview Settings Migration')
            ));

            $observer->getEndpoint()->addFieldToScript('event', array(
                'id' => 'moduleName',
                'name' => $helper->__('Specific Module'),
                'type' => 'select',
                'depends' => array(
                    array( 'id' => 'jobName', 'values' => array( 'previewMigration' ))
                ),
                'typeProperties' => array(
                    'options' => $helper->getBrontoModules(),
                    'default' => 'all'
                )
            ));
        }
    }

    /**
     * Returns the JSON for the old settings
     */
    public function previewMigration($observer)
    {
        $helper = Mage::helper('brontosoftware_migration');
        if (!$helper->isModuleOutputEnabled('Bronto_Common')) {
            return false;
        }
        $results = array();
        $script = $observer->getScript()->getObject();
        $registration = $observer->getScript()->getRegistration();
        $middleware = Mage::getModel('brontosoftware_connector/impl_connector_middleware');
        $stores = $middleware->storeScopes($registration, true);
        $defaultSettings = array();
        if ($registration->getScope() != 'store') {
            foreach ($helper->getSelectedModules($script['data']['moduleName']) as $id => $moduleName) {
                $defaultSettings[] = array(
                    'module' => $helper->__($moduleName),
                    'fields' => $helper->getScanner($id)
                        ->setScope($registration->getScope())
                        ->setScopeId($registration->getScopeId())
                        ->getSettings()
                );
            }
            $results['config'] = array(
                'name' => $registration->getScope() == 'default' ?
                    $helper->__('Default Config') :
                    Mage::app()->getWebsite($registration->getScopeId())->getName(),
                'fields' => $defaultSettings
            );
        }
        foreach ($stores as $storeCode => $storeId) {
            $settings = array();
            foreach ($helper->getSelectedModules($script['data']['moduleName']) as $id => $moduleName) {
                if (!$helper->isEnabled($id, 'store', $storeId)) {
                    continue;
                }
                $module = $helper->getScanner($id)
                    ->setScope('store')
                    ->setScopeId($storeId)
                    ->getSettings();
                if (!empty($module)) {
                    $settings[] = array(
                        'module' => $helper->__($moduleName),
                        'fields' => $module
                    );
                }
            }
            if (!empty($settings)) {
                $store = Mage::app()->getStore($storeId);
                if (!array_key_exists('stores', $results)) {
                    $results['stores'] = array();
                }
                $results['stores'][$storeCode] = array(
                    'name' => $store->getName(),
                    'modules' => $settings
                );
            }
        }
        $observer->getScript()->setResults(array(array('context' => $results)));
    }
}
