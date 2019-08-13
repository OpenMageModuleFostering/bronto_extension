<?php

class Brontosoftware_Email_Model_Template_Mailer extends Mage_Core_Model_Email_Template_Mailer
{
    protected $_helper;
    protected $_platform;
    protected $_queueManager;

    /**
     * @see parent
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->_helper = Mage::getModel('brontosoftware_email/settings');
        $this->_platform = Mage::getModel('brontosoftware_connector/impl_connector_platform');
        $this->_queueManager = Mage::getModel('brontosoftware_connector/impl_connector_queue');
    }

    /**
     * @see parent
     */
    public function send()
    {
        $storeId = $this->getStoreId();
        $messageId = $this->_helper->getLookup($this->getTemplateId(), 'store', $storeId, true);
        if (empty($messageId)) {
            return parent::send();
        }
        $message = $this->_helper->getMessage('mapping', $messageId, $storeId);
        if (empty($message)) {
            return parent::send();
        }
        $designParams = array('area' => 'frontend', 'store' => $this->getStoreId());
        $source = Mage::getModel('brontosoftware_email/event_source')
            ->setHelper($this->_helper)
            ->setDesignParams($designParams)
            ->setMailer($this);
        $action = $source->action($message);
        if (!empty($action)) {
            $context = array();
            if ($message['isSendingQueued']) {
                $context = $this->_helper->getExtraFields($message, $this->getTemplateParams());
                if (!empty($context)) {
                    $context = array(
                        'event' => array(
                            'delivery' => array(
                                'storeId' => $storeId,
                                'area' => $designParams['area'],
                                'message' => $message + array('options' => $designParams),
                                'context' => $context
                            )
                        )
                    );
                }
            }
            $event = $this->_platform->annotate($source, $message, $action, $storeId, $context);
            while (!empty($this->_emailInfos)) {
                $emailInfo = array_pop($this->_emailInfos);
                $emails = $emailInfo->getToEmails();
                if (count($emailInfo->getBccEmails())) {
                    if ($this->_helper->isForceMagento('store', $this->getStoreId())) {
                        Mage::getModel('core/email_template')
                            ->setDesignConfig($designParams)
                            ->setForceMagento(true)
                            ->setQueue($this->getQueue())
                            ->sendTransactional(
                                $this->getTemplateId(),
                                $this->getSender(),
                                $emailInfo->getBccEmails(),
                                array(),
                                $this->getTemplateParams(),
                                $this->getStoreId());
                    } else {
                        $emails = array_merge($emails, $emailInfo->getBccEmails());
                    }
                }
                foreach ($emails as $email) {
                    $event['data']['delivery']['recipients'] = array(array(
                        'deliveryType' => 'selected',
                        'id' => $email,
                        'type' => 'contact'
                    ));
                    if ($message['isSendingQueued'] || !$this->_platform->dispatch($event)) {
                        $this->_queueManager->save($event);
                    }
                }
            }
        }
        return $this;
    }
}
