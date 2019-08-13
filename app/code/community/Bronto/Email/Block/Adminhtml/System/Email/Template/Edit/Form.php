<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Block_Adminhtml_System_Email_Template_Edit_Form extends Mage_Adminhtml_Block_System_Email_Template_Edit_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        if (!Mage::helper('bronto_email')->isEnabled()) {
            return parent::_prepareForm();
        }

        parent::_prepareForm();

        /* @var $form Varien_Data_Form */
        $form = $this->getForm();

        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->getElement('base_fieldset');

        $templateId = $this->getEmailTemplate()->getId();

        if ($templateId) {
            if (!$this->getEmailTemplate()->getSystemConfigPathsWhereUsedCurrently()) {
                $fieldset->addField('used_default_for', 'label', array(
                    'label' => Mage::helper('adminhtml')->__('Used as Default For'),
                    'container_id' => 'used_default_for',
                    'after_element_html' =>
                        '<script type="text/javascript">' .
                        (!(bool)$this->getEmailTemplate()->getOrigTemplateCode() ? '$(\'' . 'used_default_for' . '\').hide(); ' : '') .
                        '</script>',
                ));

                $fieldset->addField('note_used_currently', 'label', array(
                    'label' => '',
                    'container_id' => 'note_used_currently',
                    'after_element_html' => '<div style="color:red;"><strong>Note:</strong> This Email Message is currently not used.</div>',
                ));
            }
        }

        $fieldset->removeField('template_text');
        $fieldset->removeField('template_styles');
        $fieldset->removeField('insert_variable');
        $fieldset->removeField('template_subject');

        $fieldset->removeField('template_code');
        $fieldset->addField('template_code', 'text', array(
            'name'     =>'template_code',
            'label'    => Mage::helper('adminhtml')->__('Name'),
            'required' => true
        ));

        if (!Mage::app()->isSingleStoreMode()) {
           $event = $fieldset->addField('store_id', 'select', array(
                   'name'      => 'store_id',
                   'label'     => Mage::helper('adminhtml')->__('Store View'),
                   'title'     => Mage::helper('adminhtml')->__('Store View'),
                   'onchange' => "updateMessages(this);",
                   'required'  => true,
                   'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
           ));
           $event->setAfterElementHtml("
            <script type=\"text/javascript\">
                function updateMessages(store){
                    var reloadurl = '". $this->getUrl('adminhtml/system_email_template/ajaxlist')."id/'+store.value;
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        onLoading: function (transport) {
                            $('bronto_message_id').update('Searching...');
                        },
                        onComplete: function(transport) {
                                $('bronto_message_id').update(transport.responseText);
                        }
                    });
                }
            </script>");
        } else {
           $fieldset->addField('store_id', 'hidden', array(
                   'name'      => 'store_id',
                   'value'     => Mage::app()->getStore(true)->getId()
           ));
        }


        $fieldset->addField('bronto_message_id', 'select', array(
            'name'     => 'bronto_message_id',
            'label'    => Mage::helper('adminhtml')->__('Bronto Message'),
            'values'   => Mage::helper('bronto_reminder/message')->getAllMessageOptions(),
            'required' => true,
        ));

        $fieldset->addField('template_variables_key', 'label', array(
            'container_id' => 'template_variables_key_row',
            'label' => Mage::helper('adminhtml')->__('Variables'),
            'after_element_html' => '<div id="template_variables_key_list"></div>' .
                ($templateId ? '' : '<script>$("template_variables_key_row").hide();</script>')
        ));

        if ($templateId) {
            $form->addValues($this->getEmailTemplate()->getData());
            $form->addValues(array(
                'template_variables' => Zend_Json::encode($this->getEmailTemplate()->getVariablesOptionArray(true)),
            ));
        }

        if ($values = Mage::getSingleton('adminhtml/session')->getData('email_template_form_data', true)) {
            $form->setValues($values);
        }

        return $this;
    }
}
