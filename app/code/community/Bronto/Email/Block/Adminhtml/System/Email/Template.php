<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Block_Adminhtml_System_Email_Template extends Mage_Adminhtml_Block_System_Email_Template
{
    /**
     * Set transactional emails grid template
     */
    protected function _construct()
    {
        Mage_Adminhtml_Block_Template::_construct();
        $this->setTemplate('bronto/email/template/list.phtml');
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::_prepareLayout();
        }

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Add Bronto Message'),
                    'onclick'   => "window.location='" . $this->getCreateUrl() . "'",
                    'class'     => 'add'
                ))
        );

        $this->setChild('import_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Import Existing Templates'),
                    'onclick'   => "window.location='" . $this->getImportUrl() . "'",
                    'class'     => 'go'
                ))
        );

        if (Mage::helper('bronto_email')->isLogEnabled()) {
            $this->setChild('log_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('adminhtml')->__('Delivery Log'),
                        'onclick'   => "window.location='" . $this->getLogUrl() . "'",
                        'class'     => 'go'
                    ))
            );
        }

        $this->setChild('grid', $this->getLayout()->createBlock('adminhtml/system_email_template_grid', 'email.template.grid'));

        return Mage_Adminhtml_Block_Template::_prepareLayout();
    }

    /**
     * Get transactional emails page header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::getHeaderText();
        }

        return Mage::helper('bronto_email')->__('Bronto Transactional Emails');
    }

    /**
     * Get URL for create new email template
     *
     * @return string
     */
    public function getLogUrl()
    {
        return $this->getUrl('*/system_email_log/index');
    }

    /**
     * Get URL to import existing email templates
     *
     * @return string
     */
    public function getImportUrl()
    {
        return $this->getUrl('*/system_email_template/import');
    }
}
