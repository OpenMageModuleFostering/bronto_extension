<?php

/**
 * @package     Bronto\Reminder
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.5.0
 */
class Bronto_Reminder_Block_Adminhtml_Reminder_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $model = Mage::registry('current_reminder_rule');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'  => Mage::helper('bronto_reminder')->__('General'),
            'comment' => Mage::helper('bronto_reminder')->__('Reminder emails may promote a shopping cart price rule with or without coupon. If a shopping cart price rule defines an auto-generated coupon, this reminder rule will generate a random coupon code for each customer.'),
        ));

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name'     => 'name',
            'label'    => Mage::helper('bronto_reminder')->__('Rule Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name'  => 'description',
            'label' => Mage::helper('bronto_reminder')->__('Description'),
            'style' => 'width: 98%; height: 100px;',
        ));

        if (class_exists('Mage_Adminhtml_Block_Promo_Widget_Chooser', false)) {

            $field = $fieldset->addField('salesrule_id', 'note', array(
                'name'  => 'salesrule_id',
                'label' => Mage::helper('bronto_reminder')->__('Shopping Cart Price Rule'),
                'class' => 'widget-option',
                'value' => $model->getSalesruleId(),
                'note'  => Mage::helper('bronto_reminder')->__('Promotion rule this reminder will advertise.'),
            ));

            $model->unsSalesruleId();
            $helperBlock = $this->getLayout()->createBlock('adminhtml/promo_widget_chooser');

            if ($helperBlock instanceof Varien_Object) {
                $helperBlock->setConfig($this->getChooserConfig())
                    ->setFieldsetId($fieldset->getId())
                    ->setTranslationHelper(Mage::helper('salesrule'))
                    ->prepareElementHtml($field);
            }

        }

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids','multiselect',array(
                'name'     => 'website_ids',
                'required' => true,
                'label'    => Mage::helper('newsletter')->__('Assigned to Websites'),
                'values'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(),
                'value'    => $model->getWebsiteIds()
            ));
        }

        $fieldset->addField('is_active', 'select', array(
            'label'    => Mage::helper('bronto_reminder')->__('Status'),
            'name'     => 'is_active',
            'required' => true,
            'options'  => array(
                '1' => Mage::helper('bronto_reminder')->__('Active'),
                '0' => Mage::helper('bronto_reminder')->__('Inactive'),
            ),
        ));

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('active_from', 'date', array(
            'name'   => 'active_from',
            'label'  => Mage::helper('bronto_reminder')->__('Active From'),
            'title'  => Mage::helper('bronto_reminder')->__('Active From'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('active_to', 'date', array(
            'name'   => 'active_to',
            'label'  => Mage::helper('bronto_reminder')->__('Active To'),
            'title'  => Mage::helper('bronto_reminder')->__('Active To'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $subfieldset = $form->addFieldset('sub_fieldset', array(
            'legend'  => Mage::helper('bronto_reminder')->__('Repeat Schedule'),
            'comment' => '
                By default, a rule will only send a Reminder Email to a customer once.
                To allow a rule to re-send a message (as long as the conditions still match) to a customer, you must configure the Repeat Schedule.
            ',
        ));

        $subfieldset->addField('schedule', 'text', array(
            'name'  => 'schedule',
            'label' => Mage::helper('bronto_reminder')->__('Schedule (Days)'),
            'note'  => '
                Enter days, comma-separated.<br/>
                <strong>Examples:</strong><br/>
                "<span style="font-family:monospace">0</span>": Message to be sent again the same day.<br/>
                "<span style="font-family:monospace">1</span>": Message to be sent again the next day.<br/>
            ',
        ));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getChooserConfig()
    {
        return array(
            'button' => array('open'=>'Select Rule...'),
            'type' => 'adminhtml/promo_widget_chooser_rule'
        );
    }
}
