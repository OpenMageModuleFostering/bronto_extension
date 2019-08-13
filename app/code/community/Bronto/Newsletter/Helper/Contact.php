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
    const XML_PATH_LISTS = 'bronto_newsletter/contacts/lists';

    /**
     * @return bool
     */
    public function getUpdateStatus()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_UPDATE_STATUS);
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
     * Get the list object from list id
     * @param int $listId
     * @return boolean|Bronto_Api_List_Row
     */
    public function getListData($listId)
    {
        if ($api = $this->getApi()) {
            /* @var $listObject Bronto_Api_List */
            $listObject = $api->getListObject();
            foreach ($listObject->readAll()->iterate() as $list/* @var $list Bronto_Api_List_Row */) {
                if ($list->id == $listId) {
                    return $list;
                }
            }
        }

        return false;
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

    /**
     * Convert Magento Newsletter Subscriber Status to Bronto API Contact Status
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @return boolean
     */
    public function getQueueStatus(Mage_Newsletter_Model_Subscriber $subscriber)
    {
        // Set correct status based on subscriber status
        switch ($subscriber->getStatus()) {
            case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                $status = Bronto_Api_Contact::STATUS_ACTIVE;
                break;

            case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                $status = Bronto_Api_Contact::STATUS_UNSUBSCRIBED;
                break;

            case Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED:
                $status = Bronto_Api_Contact::STATUS_UNCONFIRMED;
                break;

            case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
            default:
                $status = Bronto_Api_Contact::STATUS_TRANSACTIONAL;
                break;
        }

        return $status;
    }
}
