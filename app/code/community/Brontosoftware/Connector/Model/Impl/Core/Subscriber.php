<?php

class Brontosoftware_Connector_Model_Impl_Core_Subscriber implements Brontosoftware_Magento_Core_Subscriber_ManagerInterface
{
    protected $_subscriberCache = array();

    /**
     * @see parent
     */
    public function getById($subscriberId)
    {
        if (!array_key_exists($subscriberId, $this->_subscriberCache)) {
            $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
            if (!$subscriber->getId()) {
                $subscriber = null;
            }
            $this->_subscriberCache[$subscriberId] = $subscriber;
        }
        return $this->_subscriberCache[$subscriberId];
    }

    /**
     * @see parent
     */
    public function getByEmail($email)
    {
        $email = strtolower($email);
        if (!array_key_exists($email, $this->_subscriberCache)) {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            if (!$subscriber->getId()) {
                $subscriber = null;
            }
            $this->_subscriberCache[$email] = $subscriber;
        }
        return $this->_subscriberCache[$email];
    }

    /**
     * @see parent
     */
    public function unsubscribe($email, $ignoreStatus = false, $location = 'normal')
    {
        if ($subscriber = $this->getByEmail($email)) {
            try {
                $subscriber
                    ->setIgnoreStatus($ignoreStatus)
                    ->setLocation($location)
                    ->unsubscribe();
            } catch (Exception $e) {
                Mage::getSingleton('brontosoftware_connector/impl_core_logger')->critical($e);
                return true;
            }
        }
        return true;
    }

    /**
     * @see parent
     */
    public function subscribe($email, $ignoreStatus = false, $location = 'normal')
    {
        $subscriber = Mage::getModel('newsletter/subscriber');
        try {
            $email = strtolower($email);
            $subscriber
                ->setIgnoreStatus($ignoreStatus)
                ->setLocation($location)
                ->subscribe($email);
            $this->_subscriberCache[$email] = $subscriber;
            return true;
        } catch (Exception $e) {
            Mage::getSingleton('brontosoftware_connector/impl_core_logger')->critical($e);
            return false;
        }
    }

    /**
     * @see parent
     */
    public function getCollection()
    {
        return Mage::getModel('newsletter/subscriber')->getCollection();
    }
}
