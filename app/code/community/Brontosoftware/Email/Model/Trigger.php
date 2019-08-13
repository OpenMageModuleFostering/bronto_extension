<?php

class Brontosoftware_Email_Model_Trigger extends Mage_Core_Model_Abstract implements Brontosoftware_Magento_Email_TriggerInterface
{
    /**
     * @see parent
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('brontosoftware_email/trigger');
    }

    /**
     * @see parent
     */
    public function getSiteId()
    {
        return $this->getData(self::FIELD_SITE_ID);
    }

    /**
     * @see parent
     */
    public function getStoreId()
    {
        return $this->getData(self::FIELD_STORE_ID);
    }

    /**
     * @see parent
     */
    public function getTriggeredAt()
    {
        return $this->getData(self::FIELD_TRIGGERED_AT);
    }

    /**
     * @see parent
     */
    public function getModelType()
    {
        return $this->getData(self::FIELD_MODEL_TYPE);
    }

    /**
     * @see parent
     */
    public function getModelId()
    {
        return $this->getData(self::FIELD_MODEL_ID);
    }

    /**
     * @see parent
     */
    public function getMessageId()
    {
        return $this->getData(self::FIELD_MESSAGE_ID);
    }

    /**
     * @see parent
     */
    public function getMessageType()
    {
        return $this->getData(self::FIELD_MESSAGE_TYPE);
    }

    /**
     * @see parent
     */
    public function getSentMessage()
    {
        return $this->getData(self::FIELD_SENT_MESSAGE);
    }

    /**
     * @see parent
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::FIELD_CUSTOMER_EMAIL);
    }

    /**
     * @see parent
     */
    public function setCustomerEmail($email)
    {
        $this->setData(self::FIELD_CUSTOMER_EMAIL, $email);
        return $this;
    }

    /**
     * @see parent
     */
    public function setSentMessage($value)
    {
        $this->setData(self::FIELD_SENT_MESSAGE, $value);
        return $this;
    }

    /**
     * @see parent
     */
    public function setTriggeredAt($newTime)
    {
        $this->setData(self::FIELD_TRIGGERED_AT, $newTime);
        return $this;
    }

    /**
     * @see parent
     */
    public function setModel($modelType, $modelId, $storeId)
    {
        $this->setData(self::FIELD_MODEL_TYPE, $modelType);
        $this->setData(self::FIELD_MODEL_ID, $modelId);
        $this->setData(self::FIELD_STORE_ID, $storeId);
        return $this;
    }
}
