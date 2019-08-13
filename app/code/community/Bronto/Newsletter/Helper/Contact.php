<?php

/**
 * @package   Newsletter
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.3.5
 */
class Bronto_Newsletter_Helper_Contact extends Bronto_Common_Helper_Contact
{

    /**
     * Description for const
     */
    const XML_PATH_UPDATE_STATUS = 'bronto_newsletter/contacts/update_status';

    /**
     * Description for const
     */
    const XML_PATH_LISTS         = 'bronto_newsletter/contacts/lists';

    /**
     * @param string                 $email       
     * @param string                 $customSource
     * @return Bronto_Api_Contact_Row
     */
    public function getContactByEmail($email, $customSource = 'bronto_newsletter', $store = null)
    {
        if ($contact = parent::getContactByEmail($email, $customSource, $store)) {
            if ($this->getUpdateStatus()) {
                // We want to use the Newsletter status
                $contact = $this->setStatusFromNewsletter($contact);
            }

            $contact = $this->_addContactToLists($contact, $this->getListIds($store));
        }

        return $contact;
    }

    /**
     * @param Bronto_Api_Contact_Row           $contact   
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @return Bronto_Api_Contact_Row          
     */
    public function setStatusFromNewsletter(Bronto_Api_Contact_Row $contact, Mage_Newsletter_Model_Subscriber $subscriber = null)
    {
        if (!is_object($subscriber) || !($subscriber instanceOf Mage_Newsletter_Model_Subscriber)) {
            /* @var $subscriber Mage_Newsletter_Model_Subscriber */
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($contact->email);
        }

        switch ($subscriber->getStatus()) {
            case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                $contact->status = Bronto_Api_Contact::STATUS_ONBOARDING;
                break;
            case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                $contact->status = Bronto_Api_Contact::STATUS_UNSUBSCRIBED;
                break;
            case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
            default:
                $contact->status = Bronto_Api_Contact::STATUS_TRANSACTIONAL;
                break;
        }

        // Special check for old Magento versions
        if (defined('Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED')) {
            if (Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED == $subscriber->getStatus()) {
                $contact->status = Bronto_Api_Contact::STATUS_UNCONFIRMED;
            }
        }

        $this->writeInfo("  Setting Contact ({$contact->email}) status to: {$contact->status}");
        return $contact;
    }

    /**
     * @return bool
     */
    public function getUpdateStatus()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_UPDATE_STATUS);
    }

    /**
     * @return array
     */
    public function getListIds($store = null)
    {
        $listIds = Mage::getStoreConfig(self::XML_PATH_LISTS, $store);
        if (empty($listIds)) {
            return array();
        }

        if (!is_array($listIds)) {
            $listIds = explode(',', $listIds);
        }

        return $listIds;
    }

    /**
     * @param Bronto_Api_Contact_Row $contact
     * @param array                  $listIds
     * @return Bronto_Api_Contact_Row
     */
    protected function _addContactToLists(Bronto_Api_Contact_Row $contact, array $listIds = array())
    {
        if (empty($listIds)) {
            return $contact;
        }

        foreach ($listIds as $listId) {
            $this->writeInfo("  Adding Contact to list: {$listId}");
            $contact->addToList($listId);
        }

        return $contact;
    }

    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        return 'Bronto_Newsletter';
    }
}
