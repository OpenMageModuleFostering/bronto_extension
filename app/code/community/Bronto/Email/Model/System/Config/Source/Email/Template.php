<?php


class Bronto_Email_Model_System_Config_Source_Email_Template extends Mage_Adminhtml_Model_System_Config_Source_Email_Template
{    

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {               
        if(!$collection = Mage::registry('config_system_email_template')) {
            if (Mage::helper('bronto_email')->isEnabled() && Mage::app()->getRequest()->getParam('store')) {
                if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
                    $store      = Mage::app()->getStore($storeCode);
                    $storeId = $store->getId();
                }                 
                $collection = Mage::getResourceModel('core/email_template_collection')
                    ->addFieldToFilter('store_id', $storeId)
                    ->load();
                    
                Mage::register('config_system_email_template', $collection);
            } else {
                $collection = Mage::getResourceModel('core/email_template_collection')                    
                    ->load();

                Mage::register('config_system_email_template', $collection);
            }
        }
        $options = $collection->toOptionArray();        
        $templateName = Mage::helper('adminhtml')->__('Default Template from Locale');
        $nodeName = str_replace('/', '_', $this->getPath());
        $templateLabelNode = Mage::app()->getConfig()->getNode(self::XML_PATH_TEMPLATE_EMAIL . $nodeName . '/label');
        if ($templateLabelNode) {
            $templateName = Mage::helper('adminhtml')->__((string)$templateLabelNode);
            $templateName = Mage::helper('adminhtml')->__('%s (Default Template from Locale)', $templateName);
        }
        array_unshift(
            $options,
            array(
                'value'=> $nodeName,
                'label' => $templateName
            )
        );
        return $options;
    }

}
