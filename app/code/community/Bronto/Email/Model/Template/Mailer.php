<?php
/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.6
 */
class Bronto_Email_Model_Template_Mailer
    extends Mage_Core_Model_Email_Template_Mailer
{
    /**
     * Send all emails from email list
     * @see self::$_emailInfos
     *
     * @return Bronto_Email_Model_Template_Mailer
     */
    public function send()
    {
        // Try loading template
        $emailTemplate = Mage::getModel('bronto_email/template');
        $emailTemplate->load($this->getTemplateId());

        // If sending through bronto is not enabled, push through parent
        if (!Mage::helper('bronto_email')->canSendBronto($emailTemplate)) {
            return parent::send();
        }

        // Load Bronto Message
        $store = Mage::getModel('core/store')->load($this->getStoreId());

        // Load Bronto Message
        /* @var $messageObject Bronto_Api_Message */
        $messageObject = Mage::helper('bronto_email/message')->getApi(
            NULL,
            $store->getId(),
            $store->getWebsiteId()
        )->getMessageObject();

        // Load Message
        try {
            /* @var $message Bronto_Api_Message_Row */
            $message = $messageObject->createRow();
            $message->id = $emailTemplate->getBrontoMessageId();
            $message->read();
        }
        catch (Exception $e) {
            Mage::helper('bronto_email')->writeDebug('Falling Back to Magento Sending: ' . $e);

            return parent::send();
        }

        // Send all emails from corresponding list
        while (!empty($this->_emailInfos)) {
            $emailInfo = array_pop($this->_emailInfos);

            // Handle "Bcc" recepients of the current email
            if ($emailTemplate->getTemplateSendType() == 'magento') {
                $emailTemplate->addBcc($emailInfo->getBccEmails());
            }
            else {
                foreach ($emailInfo->getBccEmails() as $bcc) {
                    $emailInfo->addTo($bcc);
                }
            }

            // Set required design parameters and delegate email sending to Mage_Core_Model_Email_Template
            $emailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
            ->sendTransactional(
                    $message,
                    $this->getSender(),
                    $emailInfo->getToEmails(),
                    $emailInfo->getToNames(),
                    $this->getTemplateParams(),
                    $this->getStoreId()
                );
        }

        return $this;
    }
}
