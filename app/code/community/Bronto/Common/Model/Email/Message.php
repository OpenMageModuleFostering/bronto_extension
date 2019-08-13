<?php

/**
 * @package     Bronto\Common
 * @copyright   2011-2012 Bronto Software, Inc.
 * @version     1.6.7
 */
class Bronto_Common_Model_Email_Message extends Mage_Core_Model_Email_Template
{
    /**
     * @var string
     */
    protected $_helper = 'bronto_common';

    /**
     * @var Bronto_Api_Message_Row
     */
    protected $_message;

    /**
     * @var Bronto_Common_Model_Email_Message_Filter
     */
    protected $_messageFilter;

    /**
     * @var string
     */
    protected $_lastDeliveryId;

    /**
     * @var Bronto_Email_Model_Log
     */
    protected $_log;

    /**
     * Get the message currently set
     *
     * @return boolean|Bronto_Api_Message_Row False if no message is set
     */
    public function getMessage()
    {
        if (empty($this->_message)) {
            $messageId = $this->getBrontoMessageId();
            if (!empty($messageId)) {
                $this->_message = Mage::helper($this->_helper . '/message')->getMessageById($messageId);
            } else {
                return false;
            }
        }
        return $this->_message;
    }

    /**
     * Set the message
     *
     * @param Bronto_Api_Message_Row $message
     */
    public function setMessage(Bronto_Api_Message_Row $message)
    {
        $this->_message = $message;
    }

    /**
     * Get filter object for template processing logic
     *
     * @return Bronto_Common_Model_Email_Message_Filter
     */
    public function getMessageFilter()
    {
        if (empty($this->_messageFilter)) {
            $this->_messageFilter = Mage::getModel('bronto_common/email_message_filter');
        }
        return $this->_messageFilter;
    }

    /**
     * Process email template code
     *
     * @param Bronto_Api_Delivery_Row $delivery
     * @param array                   $variables
     *
     * @return Bronto_Api_Delivery_Row
     */
    public function getProcessedDelivery(Bronto_Api_Delivery_Row $delivery, array $variables = array())
    {
        $processor = $this->getMessageFilter();

        if (isset($variables['subscriber']) && ($variables['subscriber'] instanceof Mage_Newsletter_Model_Subscriber)) {
            $processor->setStoreId($variables['subscriber']->getStoreId());
        }

        if ($message = $this->getMessage()) {
            $processor->setMessageId($message->id);
        }

        $processor->setVariables($variables);
        $processor->setAvailable($this->getVariablesOptionArray());

        return $processor->filter($delivery);
    }

    /**
     * If this message can be used for sending queue as main template
     *
     * @return boolean
     */
    public function isMessageValidForSend()
    {
        /* @var $message Bronto_Api_Message_Row */
        $message = $this->getMessage();

        if (!($message instanceOf Bronto_Api_Message_Row)) {
            Mage::helper($this->_helper)->writeError('  Invalid Message');
            return false;
        }

        if ($message->status != 'active') {
            Mage::helper($this->_helper)->writeError('  Message is not active: ' . $message->name);
            return false;
        }

        if (!($this->getSenderName() && $this->getSenderEmail())) {
            Mage::helper($this->_helper)->writeError('  Message cannot be sent');
            return false;
        }

        return true;
    }

    /**
     * Send mail to recipient
     *
     * @param array|string      $email     E-mail(s)
     * @param array|string|null $name      receiver name(s)
     * @param array             $variables template variables
     *
     * @return boolean
     */
    public function send($email, $name = null, array $variables = array())
    {
        if (!Mage::helper($this->_helper)->isEnabled()) {
            return parent::send($email, $name, $variables);
        }

        /* @var $message Bronto_Api_Message_Row */
        $message   = $this->getMessage();
        $messageId = $this->getBrontoMessageId();

        if (empty($messageId)) {
            return parent::send($email, $name, $variables);
        }

        if (!$this->isMessageValidForSend()) {
            return false;
        }

        $emails = array_values((array) $email);
        $names  = is_array($name) ? $name : (array)$name;
        $names  = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name']  = reset($names);
        // Load Bronto Contact(s)
        $contacts = array();
        foreach ($emails as $key => $email) {
            Mage::helper($this->_helper . '/contact')->writeDebug('  Getting Contact Object for: ' . $email . ' - Store: ' . $variables['store']->getId());
            $contacts[$key] = Mage::helper($this->_helper . '/contact')->getContactByEmail($email, $this->_helper, $variables['store']->getId(), 2);
        }

        $deliveryCount  = 0;
        $deliveryErrors = 0;
        /* @var $contact Bronto_Api_Contact_Row */
        foreach ($contacts as $key => $contact) {
            try {
                if (!$contact->id || empty($contact->id)) {
                    $contact = Mage::helper($this->_helper . '/contact')->saveContact($contact);
                    if (!$contact->id || empty($contact->id)) {
                        $this->_beforeSend($contact, $message);
                        $deliveryErrors++;
                        Mage::helper($this->_helper)->writeDebug('  TEST MODE: Skipping e-mail: ' . $contact->email);
                        $this->_afterSend(0, "TEST MODE ENABLED: Contact does not exist: " . $contact->email);
                        continue;
                    }
                }
                $this->_beforeSend($contact, $message);

                /* @var $deliveryObject Bronto_Api_Delivery */
                Mage::helper($this->_helper)->writeDebug('  Getting Delivery Object...');
                $deliveryObject = Mage::helper($this->_helper)
                    ->getApi(null, $variables['store']->getId())
                    ->getDeliveryObject();
                $deliveryCount++;

                /* @var $delivery Bronto_Api_Delivery_Row */
                $delivery = $deliveryObject->createRow();
                $delivery->start      = date('c');
                $delivery->messageId  = $message->id;
                $delivery->type       = 'transactional';
                if (Mage::helper($this->_helper)->isTestModeEnabled()) {
                    $delivery->type   = 'test';
                }
                $delivery->fromEmail  = $this->getSenderEmail();
                $delivery->fromName   = $this->getSenderName();
                $delivery->replyEmail = $this->getSenderEmail();
                $delivery->recipients = array(
                    array(
                        'type' => 'contact',
                        'id'   => $contact->id,
                    ),
                );

                $delivery = $this->getProcessedDelivery($delivery, $variables);

                Mage::helper($this->_helper)->writeDebug('  Saving Delivery...');

                $delivery->save();
                if ($delivery->id) {
                    $this->setLastDeliveryId($delivery->id);
                    $this->_afterSend(true, null, $delivery);
                } else {
                    $deliveryErrors++;
                    $this->_afterSend(false, null, $delivery);
                }
            } catch (Exception $e) {
                if ($e->getCode() === Bronto_Api_Delivery_Exception::MESSAGE_NOT_TRANSACTIONAL_APPROVED) {
                    $this->setBrontoMessageApproved(0);
                    $this->save();
                }
                $deliveryErrors++;
                Mage::helper($this->_helper)->writeError($e);
                $this->_afterSend(false, $e->getMessage(), isset($delivery) ? $delivery : null);
            }
        }

        return $deliveryErrors == 0;
    }

    /**
     * Send transactional email to recipient
     *
     * @param int          $templateId
     * @param string|array $sender     Sender information, can be declared as part of config path
     * @param string       $email      Recipient email
     * @param string       $name       Recipient name
     * @param array        $vars       Variables which can be used in template
     * @param int|null     $storeId
     *
     * @return Mage_Core_Model_Email_Template
     */
    public function sendTransactional($templateId, $sender, $email, $name, $vars = array(), $storeId = null)
    {
        if (!Mage::helper($this->_helper)->isEnabled() || !($templateId instanceOf Bronto_Api_Message_Row)) {
            return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        } else {
            $message = $templateId;
        }

        $this->setSentSuccess(false);
        if (($storeId === null) && $this->getDesignConfig()->getStore()) {
            $storeId = $this->getDesignConfig()->getStore();
        }

        $this->setMessage($message);
        $this->setBrontoMessageId($message->id);
        $this->setBrontoMessageName($message->name);
        $this->setBrontoMessageApproved(1);

        if (!is_array($sender)) {
            $this->setSenderName(Mage::getStoreConfig('trans_email/ident_'.$sender.'/name', $storeId));
            $this->setSenderEmail(Mage::getStoreConfig('trans_email/ident_'.$sender.'/email', $storeId));
        } else {
            $this->setSenderName($sender['name']);
            $this->setSenderEmail($sender['email']);
        }

        if (!isset($vars['store'])) {
            $vars['store'] = Mage::app()->getStore($storeId);
        }
        $this->setSentSuccess($this->send($email, $name, $vars));
        return $this;
    }

    /**
     * @param string $deliveryId
     *
     * @return Bronto_Common_Model_Email_Message
     */
    public function setLastDeliveryId($deliveryId)
    {
        $this->_lastDeliveryId = $deliveryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastDeliveryId()
    {
        return $this->_lastDeliveryId;
    }

    /**
     * @param Bronto_Api_Contact_Row $contact
     * @param Bronto_Api_Message_Row $message
     */
    protected function _beforeSend(Bronto_Api_Contact_Row $contact, Bronto_Api_Message_Row $message)
    {
    }

    /**
     * @param int                     $success
     * @param string                  $error
     * @param Bronto_Api_Delivery_Row $delivery
     */
    protected function _afterSend($success, $error = null, Bronto_Api_Delivery_Row $delivery = null)
    {
    }
}
