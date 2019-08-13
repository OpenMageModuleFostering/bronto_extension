<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Block_Adminhtml_System_Email_Template_Edit extends Mage_Adminhtml_Block_System_Email_Template_Edit
{
    public function __construct()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::__construct();
        }

        Mage_Adminhtml_Block_Widget::__construct();
        $this->setTemplate('bronto/email/template/edit.phtml');
    }

    protected function _prepareLayout()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::_prepareLayout();
        }

        parent::_prepareLayout();

        $this->unsetChild('to_plain_button');
        $this->unsetChild('to_html_button');
        $this->unsetChild('preview_button');

        $this->setChild('save_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'label'   => Mage::helper('adminhtml')->__('Save Message'),
            'onclick' => 'templateControl.save();',
            'class'   => 'save'
        )));
    }

    /**
     * Return header text for form
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::getHeaderText();
        }

        if ($this->getEditMode()) {
            return Mage::helper('adminhtml')->__('Edit Bronto Email Message');
        }

        return Mage::helper('adminhtml')->__('New Bronto Email Message');
    }

    public function getUsedDefaultForPaths($asJSON = true)
    {
        $paths = $this->getEmailTemplate()->getSystemConfigPathsWhereUsedAsDefault();
        if (Mage::helper('bronto_email')->isEnabled()) {
            if ($this->getEmailTemplate()->hasData('store_id')) {
                $paths[0]['scope_id'] = $this->getEmailTemplate()->getData('store_id');
                $paths[0]['scope'] = 'stores';
            }
        }

        $pathsParts = $this->_getSystemConfigPathsParts($paths);
        
        if($asJSON){
            return Mage::helper('core')->jsonEncode($pathsParts);
        }

        return $pathsParts;
    }
}
