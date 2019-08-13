<?php

class Brontosoftware_Email_Model_Event_Source implements Brontosoftware_Magento_Connector_Event_SourceInterface
{
    protected $_mailer;
    protected $_helper;
    protected $_options;

    /**
     * Sets the context mailer
     *
     * @param mixed $mailer
     * @return $this
     */
    public function setMailer($mailer)
    {
        $this->_mailer = $mailer;
        return $this;
    }

    /**
     * Sets the email helper
     *
     * @param mixed $helper
     * @return $this
     */
    public function setHelper($helper)
    {
        $this->_helper = $helper;
        return $this;
    }

    /**
     * Sets the template design parameters for the processing
     *
     * @param array $options
     * @return $this
     */
    public function setDesignParams(array $options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * @see parent
     */
    public function getEventType()
    {
        return 'delivery';
    }

    /**
     * @see parent
     */
    public function action($message)
    {
        return $message['sendType'] == 'nosend' ? '' : 'add';
    }

    /**
     * @see parent
     */
    public function transform($message)
    {
        $delivery = array(
            'messageId' => $message['messageId'],
            'type' => $message['sendType'],
            'start' => date('c')
        );
        $sender = $this->_mailer->getSender();
        if (is_array($sender)) {
            $delivery['fromName'] = $sender['name'];
            $delivery['fromEmail'] = $sender['email'];
        } else {
            $store = Mage::app()->getStore($this->_mailer->getStoreId());
            $delivery['fromName'] = $store->getConfig('trans_email/ident_' . $sender . '/name');
            $delivery['fromEmail'] = $store->getConfig('trans_email/ident_' . $sender . '/email');
        }
        if (!empty($message['replyTo'])) {
            $delivery['replyEmail'] = $message['replyTo'];
        }
        foreach ($message['sendFlags'] as $flag) {
            $delivery[$flag] = true;
        }
        $delivery['fields'] = $this->_helper->createDeliveryFields(
            $this->_mailer->getTemplateId(),
            $message,
            $this->_options,
            $this->_mailer->getTemplateParams());
        return $delivery;
    }
}
