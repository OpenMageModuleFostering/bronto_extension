<?php

class Brontosoftware_Connector_Block_Registration_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @see parent
     */
    protected function _prepareForm()
    {
        $model = Mage::getModel('brontosoftware_connector/registration');
        if ($this->getRequest()->has('id')) {
            $model->load($this->getRequest()->getParam('id'));
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'method' => 'post',
            'action' => $this->getData('action')
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->__('Registration Information')
        ));
        $fieldset->addType('scope', implode('_', array(get_class($this), 'Element', 'Scope')));

        $fieldset->addField('entity_id', 'hidden', array(
            'name' => 'entity_id',
        ));

        $fieldset->addField('name', 'hidden', array(
            'name' => 'name'
        ));

        $options = array(
            'SANDBOX' => $this->__('Sandbox'),
            'PRODUCTION' => $this->__('Production')
        );
        $fieldset->addField('environment', 'select', array(
            'name' => 'environment',
            'title' => $this->__('Environment'),
            'label' => $this->__('Environment'),
            'options' => $options
        ));

        $fieldset->addField('scope', 'scope', array(
            'label' => $this->__('Root Scope'),
            'title' => $this->__('Root Scope'),
            'scopeHash' => $model->getScopeHash(true),
            'required' => true,
        ));

        $fieldset->addField('connector_key', 'text', array(
            'name' => 'connector_key',
            'required' => true,
            'label' => $this->__('Account ID'),
            'title' => $this->__('Account ID')
        ));

        $protected = $fieldset->addField('is_protected', 'select', array(
            'label' => $this->__('Basic Auth Protected'),
            'title' => $this->__('Basic Auth Protected'),
            'required' => true,
            'name' => 'is_protected',
            'options' => array( 0 => $this->__('No'), 1 => $this->__('Yes') ),
            'note' => $this->__('Bronto Connector will required network communication to the admin store. If your admin store is protected by Basic Auth, then select <em>Yes</em>, and fill in the username and password below. If your admin store is protected by a firewall, then you must allow network communication for incoming and outgoing requests to <strong>middleware.brontops.com</strong> and <strong>sarlacc.brontops.com</strong>. Your credentials are only used for Bronto Connector communication on encrypted channels.')
        ));

        $username = $fieldset->addField('username', 'text', array(
            'label' => $this->__('Username'),
            'title' => $this->__('Username'),
            'name' => 'username'
        ));

        $password = $fieldset->addField('password', 'text', array(
            'label' => $this->__('Password'),
            'title' => $this->__('Password'),
            'name' => 'password'
        ));

        $dependence = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependence
            ->addFieldMap($protected->getHtmlId(), $protected->getName())
            ->addFieldMap($username->getHtmlId(), $username->getName())
            ->addFieldMap($password->getHtmlId(), $password->getName())
            ->addFieldDependence(
                $username->getName(),
                $protected->getName(),
                '1'
            )
            ->addFieldDependence(
                $password->getName(),
                $protected->getName(),
                '1'
            );
        $form->setUseContainer(true);
        $form->setValues($model->getData());
        $this->setForm($form);
        $this->setChild('form_after', $dependence);
        return $this;
    }
}
