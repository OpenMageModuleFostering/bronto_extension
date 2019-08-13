<?php

class Brontosoftware_Verify_Model_Observer
{
    /**
     * Adds the Preview Conflicts job
     */
    public function advancedAdditional($observer)
    {
        $helper = Mage::helper('brontosoftware_verify');
        $observer->getEndpoint()->addOptionToScript('event', 'jobName', array(
            'id' => 'previewConflict',
            'name' => $helper->__('Preview Module Rewrites')
        ));

        $observer->getEndpoint()->addFieldToScript('event', array(
            'id' => 'conflictArea',
            'name' => $helper->__('Extension Area'),
            'type' => 'select',
            'depends' => array(
                array('id' => 'jobName', 'values' => array('previewConflict'))
            ),
            'typeProperties' => array(
                'default' => 'all',
                'options' => $helper->getOptionArray()
            )
        ));
    }

    /**
     * The job that actually performs the conflict lookup
     */
    public function previewConflict($observer)
    {
        $results = array();
        $helper = Mage::helper('brontosoftware_verify');
        $script = $observer->getScript()->getObject();
        $config = Mage::app()->getConfig()->getNode('global');
        foreach ($helper->getSelectedAreas($script['data']['conflictArea']) as $areaName) {
            $rewrites = $this->_scanConfig($config, $areaName);
            if (empty($rewrites)) {
                continue;
            }
            $results[$areaName] = $rewrites;
        }
        $observer->getScript()->setResults(array(array('context' => $results)));
    }

    /**
     * Gets the latest rewritten areas
     *
     * @param mixed $config
     * @param string $group
     * @return array
     */
    protected function _scanConfig($config, $group) {
        $rewrites = array();
        if (isset($config->{$group})) {
            foreach ($config->{$group} as $extension) {
                foreach ($extension as $area => $node) {
                    if (isset($node->rewrite)) {
                        foreach (get_object_vars($node->rewrite) as $oldClass => $newClass) {
                            try {
                                if (!class_exists($newClass)) {
                                    throw new RuntimeException("$newClass not found.");
                                }
                            } catch (Exception $e) {
                                continue;
                            }
                            if (!array_key_exists($area, $rewrites)) {
                                $rewrites[$area] = array();
                            }
                            $rewrites[$area][$oldClass] = $newClass;
                        }
                    }
                }
            }
        }
        return $rewrites;
    }
}
