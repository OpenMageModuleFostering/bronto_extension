<?php

class Brontosoftware_Migration_Model_Scanner_Email extends Brontosoftware_Migration_Model_Scanner
{
    const MODULE_PATH = 'bronto_email/%';

    protected $_fieldsToLabel = array(
        'enabled' => 'Enabled',
        'use_bronto' => 'Allow Sending Through Bronto',
        'field_selector' => 'CSS Field Selector',
        'identity' => 'Email Sender',
        'allow_send' => 'Allow Sending Through Bronto',
        'cancel_status' => 'Order Cancel Status',
        'period' => 'Send Period',
        'multipler' => 'Send Period Per Unit',
        'message' => 'Message',
        'status' => 'Order Status',
        'url_suffix' => 'Review Form'
    );

    /**
     * @see parent
     */
    protected function _modulePath()
    {
        return self::MODULE_PATH;
    }

    /**
     * @see parent
     */
    protected function _afterConfig($settings)
    {
        $settings = parent::_afterConfig($settings);
        if (!empty($settings)) {
            $settings['reminder'] = $this->_populateSettings('bronto_reminder/%');
            $settings['reviews'] = $this->_populateSettings('bronto_reviews/%');
            $configured = array();
            foreach ($this->_settings('%_template') as $template) {
                if (!is_numeric($template->getValue())) {
                    continue;
                }
                if (!array_key_exists($template->getValue(), $configured)) {
                    $configured[$template->getValue()] = array();
                }
                $configured[$template->getValue()][] = $template->getPath();
            }
            $emails = Mage::getModel('bronto_email/template')
                ->getCollection()
                ->addFieldToFilter('store_id', array('eq' => $this->_scopeId))
                ->addFieldToFilter('template_send_type', array('neq' => 'magento'));
            foreach ($emails as $email) {
                if (!array_key_exists('templates', $settings)) {
                    $settings['templates'] = array();
                }
                $settings['templates'][] = array(
                    'id' => $email->getId(),
                    'configured' => array_key_exists($email->getId(), $configured) ?
                        $configured[$email->getId()] :
                        false,
                    'message_name' => $email->getBrontoMessageName(),
                    'message_id' => $email->getBrontoMessageId(),
                    'sales_rule' => $email->getSalesRule(),
                    'product_recommendation' => $email->getProductRecommendation(),
                    'send_flags' => $email->getSendFlags(),
                );
            }
        }
        return $settings;
    }
}
