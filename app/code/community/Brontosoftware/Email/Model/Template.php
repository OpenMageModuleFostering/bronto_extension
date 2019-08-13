<?php

class Brontosoftware_Email_Model_Template extends Mage_Core_Model_Email_Template
{
    protected $_forceMagento = false;

    /**
     * Forces a Magento send regardless
     *
     * @param boolean $forceMagento
     * @return $this
     */
    public function setForceMagento($forceMagento)
    {
        $this->_forceMagento = $forceMagento;
        return $this;
    }

    /**
     * @see parent
     */
    public function sendTransactional($templateId, $sender, $email, $name, $vars = array(), $storeId = null)
    {
        if ($this->_forceMagento) {
            return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        }
        if (!$this->getDesignConfig()->hasStore()) {
            $this->getDesignConfig()->setStore(Mage::app()->getStore()->getId());
        }
        if ($storeId === null && $this->getDesignConfig()->getStore()) {
            $storeId = $this->getDesignConfig()->getStore();
        }
        $helper = Mage::getSingleton('brontosoftware_email/settings');
        $queueManager = Mage::getSingleton('brontosoftware_connector/impl_connector_queue');
        $messageId = $helper->getLookup($templateId, 'store', $storeId, true);
        if (empty($messageId)) {
            return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        }
        $template = $helper->getMessage('mapping', $messageId, $storeId);
        if (empty($template)) {
            return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        }
        $platform = Mage::getSingleton('brontosoftware_connector/impl_connector_platform');
        $mailer = new Brontosoftware_Magento_Core_DataObject(array(
            'store_id' => $storeId,
            'template_id' => $templateId,
            'sender' => $sender,
            'template_params' => $vars
        ));
        $source = Mage::getModel('brontosoftware_email/event_source')
            ->setHelper($helper)
            ->setDesignParams($this->getDesignConfig()->getData())
            ->setMailer($mailer);
        $action = $source->action($template);
        if (!empty($action)) {
            $context = array();
            if ($template['isSendingQueued']) {
                $context = $helper->getExtraFields($template, $vars);
                if (!empty($context)) {
                    $context = array(
                        'event' => array(
                            'delivery' => array(
                                'storeId' => $storeId,
                                'area' => $this->getDesignConfig()->getArea(),
                                'message' => $template + array('options' => $this->getDesignConfig()->getData()),
                                'context' => $context
                            )
                        )
                    );
                }
            }
            $event = $platform->annotate($source, $template, $action, $storeId, $context);
            $emails = array_values((array)$email);
            if (!empty($this->_bccEmails)) {
                if ($helper->isForceMagento('store', $storeId)) {
                    Mage::getModel('core/email_template')
                        ->setDesignConfig($this->getDesignConfig()->getData())
                        ->setForceMagento(true)
                        ->setQueue($this->getQueue())
                        ->sendTransactional(
                            $templateId,
                            $sender,
                            $this->_bccEmails,
                            array(),
                            $vars,
                            $storeId);
                } else {
                    $emails = array_merge($emails, $this->_bccEmails);
                }
            }
            $success = false;
            foreach ($emails as $key => $email) {
                $event['data']['delivery']['recipients'] = array(array(
                    'deliveryType' => 'selected',
                    'id' => $email,
                    'type' => 'contact'
                ));
                if ($template['isSendingQueued']) {
                    $success = $queueManager->save($event);
                } else {
                    $success = (
                        $platform->dispatch($event) ||
                        $queueManager->save($event)
                    );
                }
            }
            $this->setSentSuccess($success);
        }
        return $this;
    }
}
