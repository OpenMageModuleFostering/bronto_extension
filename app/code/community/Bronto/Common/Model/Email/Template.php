<?php

/**
 * @package       Bronto/Common
 * @copyright (c) 2011-2012, Bronto Software, Inc.
 */
class Bronto_Common_Model_Email_Template extends Mage_Core_Model_Email_Template
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
     * @var string
     */
    protected $_lastDeliveryId;

    /**
     * @var Bronto_Email_Model_Log
     */
    protected $_log;

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
     * Get the message currently set
     *
     * @return boolean|Bronto_Api_Message_Row False if no message is set
     */
    public function getMessage()
    {
        if (empty($this->_message)) {
            $messageId = $this->getBrontoMessageId();
            if (!empty($messageId)) {
                $this->_message = Mage::helper('bronto_common/message')->getMessageById($messageId);
            } else {
                return false;
            }
        }

        return $this->_message;
    }

    /**
     * Get filter object for template processing logic
     *
     * @param null $storeId
     *
     * @return false|Mage_Core_Model_Abstract|Mage_Core_Model_Email_Template_Filter
     */
    public function getTemplateFilter($storeId = null)
    {
        if (!Mage::helper($this->_helper)->canSendBronto($this, $storeId)) {
            return parent::getTemplateFilter();
        }

        if (empty($this->_templateFilter)) {
            $this->_templateFilter = Mage::getModel('bronto_common/email_template_filter');
        }

        return $this->_templateFilter;
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
        $processor = $this->getTemplateFilter($variables['store']->getId());

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
            Mage::helper($this->_helper)->writeError('  Message cannot be sent: Sender Name or Email is not set');

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
        // If not set to go through Bronto, fall through to magento sending
        if (!Mage::helper($this->_helper)->canSendBronto($this, $variables['store']->getId())) {
            return parent::send($email, $name, $variables);
        }

        /* @var $message Bronto_Api_Message_Row */
        $message   = $this->getMessage();
        $messageId = $this->getBrontoMessageId();

        // If messageId is empty, send through Magento
        if (empty($messageId)) {
            return parent::send($email, $name, $variables);
        }

        // If message is not valid for sending, return false
        if (!$this->isMessageValidForSend()) {
            return false;
        }

        // Handle CC and BCC by simply adding as array
        $emails = array_values((array)$email);
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
            Mage::helper('bronto_common/contact')->writeDebug('  Getting Contact Object for: ' . $email . ' - Store: ' . $variables['store']->getId());
            $contacts[$key] = Mage::helper('bronto_common/contact')->getContactByEmail($email, $this->_helper, $variables['store']->getId(), 2);
        }

        $deliveryCount  = 0;
        $deliveryErrors = 0;

        /* @var $contact Bronto_Api_Contact_Row */
        foreach ($contacts as $contact) {
            try {
                // If contact does not have id, create contact, if still no id, log it
                if (!$contact->id || empty($contact->id)) {
                    $contact = Mage::helper('bronto_common/contact')->saveContact($contact);
                    if (!$contact->id || empty($contact->id)) {
                        $this->_beforeSend($contact, $message);
                        $deliveryErrors++;
                        Mage::helper($this->_helper)->writeDebug('  TEST MODE: Skipping e-mail: ' . $contact->email);
                        $this->_afterSend(0, "TEST MODE ENABLED: Contact does not exist: " . $contact->email);
                        continue;
                    }
                }

                // Trigger _beforeSend action for logging
                $this->_beforeSend($contact, $message);

                /* @var $deliveryObject Bronto_Api_Delivery */
                Mage::helper($this->_helper)->writeDebug('  Getting Delivery Object...');
                $deliveryObject = Mage::helper($this->_helper)
                    ->getApi(null, 'store', $variables['store']->getId())
                    ->getDeliveryObject();
                $deliveryCount++;
                Mage::helper($this->_helper)->writeDebug('    Delivery Object Created Successfully');

                Mage::helper($this->_helper)->writeDebug('  Creating Delivery Row...');
                /* @var $delivery Bronto_Api_Delivery_Row */
                $delivery            = $deliveryObject->createRow();
                $delivery->start     = Mage::getModel('core/date')->date('c');
                $delivery->messageId = $message->id;

                $delivery->type = $this->getTemplateSendType() ? $this->getTemplateSendType() : 'transactional';
                if (Mage::helper($this->_helper)->isTestModeEnabled()) {
                    $delivery->type = 'test';
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
                Mage::helper($this->_helper)->writeDebug('  Processing Delivery');
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
                $errorMessage = $e->getMessage();
                if ($e->getCode() === Bronto_Api_Delivery_Exception::MESSAGE_NOT_TRANSACTIONAL_APPROVED) {
                    // Replace message id with message name
                    if (preg_match_all("/([a-zA-Z0-9\-]){36}/", $errorMessage, $matches)) { // Grab field id if exists
                        foreach ($matches[0] as $match) {
                            $errorMessage = str_replace($match, $message->name, $errorMessage);
                        }
                    }
                }

                $deliveryErrors++;
                Mage::helper($this->_helper)->writeError($errorMessage);
                $this->_afterSend(false, $errorMessage, isset($delivery) ? $delivery : null);
            }
        }

        return $deliveryErrors == 0;
    }

    /**
     * Send transactional email to recipient
     *
     * @param int          $templateId
     * @param string|array $sender Sender information, can be declared as part of config path
     * @param string       $email  Recipient email
     * @param string       $name   Recipient name
     * @param array        $vars   Variables which can be used in template
     * @param int|null     $storeId
     *
     * @return Mage_Core_Model_Email_Template
     */
    public function sendTransactional($templateId, $sender, $email, $name, $vars = array(), $storeId = null)
    {
        // If Template ID is 'nosend', then simply return false
        if ($templateId == 'nosend') {
            return false;
        }

        // If not set to go through Bronto, fall through to magento sending
        if (!Mage::helper($this->_helper)->canSendBronto($this, $storeId)) {
            return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        } else {
            // If module enabled and template ID is not an instance of the api row, see if we can pull an instance
            if (!($templateId instanceOf Bronto_Api_Message_Row)) {
                $emailTemplate = Mage::getModel('bronto_email/template')
                    ->setDesignConfig($this->getDesignConfig()->getData());

                // If $templateId is numeric, load template by ID, else is default and should send through Magento
                if (is_numeric($templateId)) {
                    $emailTemplate->load($templateId);
                } else {
                    $this->setTemplateSendType('magento');
                    return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                }

                // If Template doesn't have a Bronto Message ID, send through magento
                if (!$emailTemplate->getBrontoMessageId() || is_null($emailTemplate->getBrontoMessageId())) {
                    return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                }

                // Load Store
                $store = Mage::getModel('core/store')->load($storeId);

                // Load Bronto Message
                /* @var $messageObject Bronto_Api_Message */
                $messageObject = Mage::helper('bronto_common/message')->getApi(null, 'store', $store->getId())->getMessageObject();

                // Load Message
                try {
                    /* @var $message Bronto_Api_Message_Row */
                    $message     = $messageObject->createRow();
                    $message->id = $emailTemplate->getBrontoMessageId();
                    $message->read();
                } catch (Exception $e) {
                    Mage::helper($this->_helper)->writeDebug('Falling Back to Magento Sending: Message Not Found.' /* . $e*/);

                    return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                }

                // Send through main template model
                $emailTemplate->sendTransactional(
                    $message,
                    $sender,
                    $email,
                    $name,
                    $vars,
                    $storeId
                );

                return $this->setSentSuccess($emailTemplate->getSentSuccess());
            } else {
                $message = $templateId;
            }
        }

        // Start the send process
        $this->setSentSuccess(false);
        if (($storeId === null) && $this->getDesignConfig()->getStore()) {
            $storeId = $this->getDesignConfig()->getStore();
        }

        $this->setMessage($message);
        $this->setBrontoMessageId($message->id);
        $this->setBrontoMessageName($message->name);
        $this->setBrontoMessageApproved(1);

        // If $sender is not array, it is a reference to a config setting, otherwise it should have 'name' and 'email'
        if (!is_array($sender)) {
            $this->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $sender . '/name', $storeId));
            $this->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $sender . '/email', $storeId));
        } else {
            $this->setSenderName($sender['name']);
            $this->setSenderEmail($sender['email']);
        }

        // If store is not set, set it
        if (!isset($vars['store'])) {
            $vars['store'] = Mage::app()->getStore($storeId);
        }

        // Check for Sales Rules
        if ($this->getSalesRule() && !isset($vars['coupon'])) {
            try {
                /** @var Mage_SalesRule_Model_Rule $rule */
                $rule = Mage::getModel('salesrule/rule')->load($this->getSalesRule());

                /** @var Mage_SalesRule_Model_Coupon $coupon */
                $coupon = $rule->acquireCoupon();

                if ($coupon) {
                    $vars['coupon'] = $coupon;
                }
            } catch (Exception $e) {
                Mage::helper($this->_helper)->writeDebug('  Failed loading Sales Rule with ID: ' . $this->getSalesRule());
            }
        }

        // Send
        $this->setSentSuccess($this->send($email, $name, $vars));

        return $this;
    }

    /**
     * @param string $deliveryId
     *
     * @return Bronto_Common_Model_Email_Template
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
