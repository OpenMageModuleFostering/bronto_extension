<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Block_Adminhtml_Reminder_Edit_Tab_Bronto extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare general properties form
     *
     * @return Bronto_Reminder_Block_Adminhtml_Reminder_Edit_Tab_Bronto
     */
    protected function _prepareForm()
    {
        $form  = new Varien_Data_Form();
        $model = Mage::registry('current_reminder_rule');

        $fieldset = $form->addFieldset('message_fieldset', array(
            'legend' => Mage::helper('bronto_reminder')->__('Bronto Messages'),
            'table_class' => 'form-list stores-tree',
            'comment' => Mage::helper('bronto_reminder')->__('Messages will be sent only for specified store views. Message store view matches the store view customer was registered on.'),
        ));

        foreach (Mage::app()->getWebsites() as $website) {
            $groups = $website->getGroups();

            if (count($groups) == 0) {
                continue;
            }
            $fieldset->addField("website_message_{$website->getId()}", 'note', array(
                'label' => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $gkey => $group) {
                $stores = $group->getStores();

                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("group_message_{$group->getId()}", 'note', array(
                    'label' => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $key => $store) {
                    if (Mage::helper('bronto_reminder')->isEnabled($store->getId())) {
                        $values = Mage::helper('bronto_reminder/message')->getMessagesOptionsArray($store->getId(), $website->getId());

                        $fieldset->addField("store_message_{$store->getId()}", 'select', array(
                              'name' => "store_messages[{$store->getId()}]",
                              'required' => false,
                              'label' => $store->getName(),
                              'values' => $values,
                              'fieldset_html_class' => 'store',
                              'disabled' => count($values) == 1 ? true : false,
                        ));
                    } else {
                        unset($stores[$key]);
                    }
                }
                if (count($stores) == 0) {
                    unset($groups[$gkey]);
                    $fieldset->removeField("group_message_{$group->getId()}");
                }
            }

            if (count($groups) == 0) {
                $fieldset->removeField("website_message_{$website->getId()}");
            }
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
