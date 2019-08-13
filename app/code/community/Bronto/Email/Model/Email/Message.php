<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Model_Email_Message extends Bronto_Common_Model_Email_Message
{
    /**
     * @var string
     */
    protected $_helper = 'bronto_email';

    /**
     * Log about the functionality of sending the email before it goes out
     *
     * @param Bronto_Api_Contact_Row $contact
     * @param Bronto_Api_Message_Row $message
     *
     * @return void
     */
    protected function _beforeSend(Bronto_Api_Contact_Row $contact, Bronto_Api_Message_Row $message)
    {
        Mage::dispatchEvent('bronto_email_send_before');

        if (Mage::helper('bronto_email')->isLogEnabled()) {
            $this->_log = Mage::getModel('bronto_email/log');
            $this->_log->setCustomerEmail($contact->email);
            $this->_log->setContactId($contact->id);
            $this->_log->setMessageId($message->id);
            $this->_log->setMessageName($message->name);
            $this->_log->setSuccess(0);
            $this->_log->setSentAt(new Zend_Db_Expr('NOW()'));
            $this->_log->save();
        }
    }

    /**
     * Log data on sending message
     *
     * @param bool                    $success
     * @param string                  $error
     * @param Bronto_Api_Delivery_Row $delivery
     *
     * @return void
     */
    protected function _afterSend($success, $error = null, Bronto_Api_Delivery_Row $delivery = null)
    {
        Mage::dispatchEvent('bronto_email_send_after');

        if (Mage::helper('bronto_email')->isLogEnabled()) {
            $this->_log->setSuccess((int) $success);
            if (!empty($error)) {
                $this->_log->setError($error);
            }
            if ($delivery) {
                $this->_log->setDeliveryId($delivery->id);
                if (Mage::helper('bronto_email')->isLogFieldsEnabled()) {
                    $this->_log->setFields(serialize($delivery->getFields()));
                }
            }
            $this->_log->save();
            $this->_log = null;
        }
    }
}
