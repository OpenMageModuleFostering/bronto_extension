<?php

class Brontosoftware_Connector_Model_Impl_Core_Inbox implements Brontosoftware_Magento_Core_Notification_InboxInterface
{
    const REDIRECT_PATH = '*/notification/redirect';

    /**
     * @see parent
     */
    public function addNotice($title, $description, $url)
    {
        try {
            $inbox = Mage::getModel('adminnotification/inbox');
            $notice = $inbox
                ->addNotice($title, $description, $url, false)
                ->loadLatestNotice();
            $notice->setUrl($this->_wrapUrl($notice->getId(), $url))->save();
        } catch (Exception $e) {
            Mage::getSingleton('brontosoftware_connector/impl_core_logger')->critical($e);
            return false;
        }
        return true;
    }

    /**
     * @see parent
     */
    public function markAsRead($notificationId)
    {
        try {
            $inbox = Mage::getModel('adminnotification/inbox')->load($notificationId);
            if ($inbox->getId()) {
                $inbox->setIsRead(1)->save();
            }
        } catch (Exception $e) {
            Mage::getSingleton('brontosoftware_connector/impl_core_logger')->critical($e);
        }
    }

    /**
     * Wraps a notification url with a redirect path to mark as read
     *
     * @param mixed $notificationId
     * @param string $url
     * @return string
     */
    protected function _wrapUrl($notificationId, $url)
    {
        return Mage::getModel('adminhtml/url')->getUrl(self::REDIRECT_PATH, array(
            'id' => $this->_encode($notificationId),
            'url' => $this->_encode($url)
        ));
    }

    /**
     * Encodes an encrypted parameter back to itself
     *
     * @param string $message
     * @return string
     */
    protected function _encode($message)
    {
        $encrypt = Mage::getModel('core/encryption');
        return rawurlencode(base64_encode($encrypt->encrypt($message)));
    }
}
