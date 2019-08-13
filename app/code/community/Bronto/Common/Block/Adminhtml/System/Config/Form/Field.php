<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Block_Adminhtml_System_Config_Form_Field extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Override method to output our custom HTML with JavaScript
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if (!extension_loaded('soap') || !extension_loaded('openssl')) {
            $element->setDisabled('disabled')->setValue(0);
        } else {
            if (!Mage::helper('bronto_common')->getApiToken()) {
                $element->setDisabled('disabled')->setValue(0);
                if ($element->getLabel() === 'Enable Module') {
                    $element->setComment('<span style="color:red;font-weight: bold">A valid Bronto API key is required.</span>');
                }
            }
        }

        return parent::_getElementHtml($element);
    }
}
