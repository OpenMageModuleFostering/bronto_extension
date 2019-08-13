<?php

/**
 * @var Mage_Adminhtml_System_Email_TemplateController
 */
require_once 'Mage/Adminhtml/controllers/System/Email/TemplateController.php';

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Adminhtml_System_Email_TemplateController extends Mage_Adminhtml_System_Email_TemplateController
{
    public function saveAction()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::saveAction();
        }

        $request = $this->getRequest();
        $id = $this->getRequest()->getParam('id');

        $template = $this->_initTemplate('id');
        if (!$template->getId() && $id) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('This Email template no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        try {


            $messages = Mage::helper('bronto_common/message')->getAllMessageOptions();
            foreach ($messages as $message) {
                if ($message['value'] == $request->getParam('bronto_message_id')) {
                    $messageName = $message['label'];
                    break;
                }
            }

            $template->setTemplateCode($request->getParam('template_code'))
                ->setBrontoMessageId($request->getParam('bronto_message_id'))
                ->setBrontoMessageName(isset($messageName) ? $messageName : 'Unknown')
                ->setModifiedAt(Mage::getSingleton('core/date')->gmtDate())
                ->setOrigTemplateCode($request->getParam('orig_template_code'))
                ->setOrigTemplateVariables($request->getParam('orig_template_variables'))
                ->setStoreId($request->getParam('store_id'));

            if (!$template->getId() || !$template->getAddedAt()) {
                $template->setAddedAt(Mage::getSingleton('core/date')->gmtDate());
            }

            $template->save();
            Mage::getSingleton('adminhtml/session')->setFormData(false);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The email template has been saved.'));
            $this->_redirect('*/*');

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->setData('email_template_form_data', $this->getRequest()->getParams());
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_forward('new');
        }
    }

    /**
     * Set template data to retrieve it in template info form
     */
    public function defaultTemplateAction()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::defaultTemplateAction();
        }

        $template = $this->_initTemplate('id');
        $templateCode = $this->getRequest()->getParam('code');

        $template->loadDefault($templateCode, $this->getRequest()->getParam('locale'));
        $template->setData('orig_template_code', $templateCode);
        $template->setData('template_variables', Zend_Json::encode($template->getVariablesOptionArray(true)));

        $templateBlock = $this->getLayout()->createBlock('adminhtml/system_email_template_edit');
        $template->setData('orig_template_used_default_for', $templateBlock->getUsedDefaultForPaths(false));

        // Edit: Remove unneeded variables
        $template->unsetData('template_styles');
        $template->unsetData('template_text');
        $template->unsetData('template_type');
        $template->unsetData('template_subject');
        // End

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($template->getData()));
    }

    public function importAction()
    {
        try {
            $importModel = Mage::getModel('bronto_email/template_import');
            $importModel->importTemplates();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The email templates have been successfully imported.'));
            $this->_redirect('*/*');

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('There was an error importing the templates.');
            $this->_redirect('*/*');
        }
    }

    public function ajaxlistAction()
    {
        $request = $this->getRequest();
        $messages = Mage::helper('bronto_common/message')->getMessagesOptionsArray($request->getParam('id'));
        foreach ($messages as $message) {
            echo sprintf('<option value="%s">%s</option>', $message['value'], $message['label']);
        }
    }
}
