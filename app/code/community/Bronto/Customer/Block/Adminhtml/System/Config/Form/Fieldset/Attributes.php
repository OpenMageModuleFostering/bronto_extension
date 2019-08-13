<?php

/**
 * @package   Bronto\Customer
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.0.0
 */
abstract class Bronto_Customer_Block_Adminhtml_System_Config_Form_Fieldset_Attributes extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_ignoreAttributes = array();
    protected $_clonedSelect;
    protected $_clonedSelectName;
    protected $_clonedText;
    protected $_clonedTextName;

    /**
     * Return header html for fieldset
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @return string
     */
    protected function _getHeaderHtml($fieldset)
    {
        $ignoreAttributeCodes = $this->_getUsedAttributeCodes($fieldset);
        $ignoreAttributeCodes = array_merge($ignoreAttributeCodes, $this->_ignoreAttributes);

        // Append any extra Customer attributes
        foreach ($this->_getAttributes() as $_attributeId => $_attribute) {
            $_attributeCode = $_attribute->getAttributeCode();
            if (in_array($_attributeCode, $ignoreAttributeCodes)) {
                continue;
            }

            if ($_label = $_attribute->getFrontendLabel()) {
                // Add select
                $appendSelect = clone $this->_clonedSelect;
                $appendSelect->setLabel($_label);
                $appendSelect->setId(str_replace($this->_clonedSelectName, $_attributeCode, $appendSelect->getId()));
                $appendSelect->setHtmlId(str_replace($this->_clonedSelectName, $_attributeCode, $appendSelect->getHtmlId()));
                $appendSelect->setName(str_replace($this->_clonedSelectName, $_attributeCode, $appendSelect->getName()));
                $fieldset->addElement($appendSelect);

                // Add custom field name input
                $appendText = clone $this->_clonedText;
                $appendText->setId(str_replace($this->_clonedTextName, "new_{$_attributeCode}", $appendText->getId()));
                $appendText->setHtmlId(str_replace($this->_clonedTextName, "new_{$_attributeCode}", $appendText->getHtmlId()));
                $appendText->setName(str_replace($this->_clonedTextName, "new_{$_attributeCode}", $appendText->getName()));
                $fieldset->addElement($appendText);

                // Field dependencies
                $this->getForm()->getChild('element_dependense')
                    ->addFieldMap($appendSelect->getHtmlId(), $appendSelect->getName())
                    ->addFieldMap($appendText->getHtmlId(), $appendText->getName())
                    ->addFieldDependence($appendText->getName(), $appendSelect->getName(), '_new_')
                ;
            }
        }

        return parent::_getHeaderHtml($fieldset);
    }

    abstract protected function _getAttributes();

    /**
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @return array<string>
     */
    protected function _getUsedAttributeCodes(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $usedAttributeCodes = array();
        foreach ($fieldset->getSortedElements() as $_element) {
            // Determine the *actual* name for this select box
            preg_match('/\[(\w+)\]\[value\]/', $_element->getName(), $matches);
            if (isset($matches[1])) {
                if (stripos($_element->getName(), 'new_') === false) {
                    $this->_clonedSelect     = $_element;
                    $this->_clonedSelectName = $matches[1];
                } else {
                    $this->_clonedText     = $_element;
                    $this->_clonedTextName = $matches[1];
                }

                // Add to list
                $usedAttributeCodes[] = $matches[1];
            }
        }

        return $usedAttributeCodes;
    }
}
