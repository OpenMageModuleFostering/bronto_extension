<?php

/**
 * @package   Roundtrip
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.1.5
 */
class Bronto_Roundtrip_Block_Adminhtml_System_Config_Status
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * Description for protected
     * @var string   
     * @access protected
     */
    protected $_module = 'bronto_roundtrip';

    /**
     * Description for protected
     * @var string   
     * @access protected
     */
    protected $_name   = 'Bronto Roundtrip Install Verification';

    /**
     * Short description for function
     * 
     * Long description (if any) ...
     * 
     * @return void  
     * @access public
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('bronto/roundtrip/status.phtml');
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string                           
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->_name;
    }
    
    /**
     * @return string
     */
    public function getRoundtripStatus()
    {
        return Mage::helper($this->_module)->getRoundtripStatusText();
    }
    
    /**
     * Short description for function
     * 
     * Long description (if any) ...
     * 
     * @return string Return description (if any) ...
     * @access public
     */
    public function getRoundtripButton()
    {
        $html = null;
        
        $html = $this->getLayout()->createBlock('bronto_roundtrip/adminhtml_widget_button_run')->toHtml();
        
        $html = "<p class=\"form-buttons\">{$html}</p>";
        
        return $html;
    }

    /**
     * @return string
     */
    public function getAppendedScripts()
    {
        return null;
    }
}
