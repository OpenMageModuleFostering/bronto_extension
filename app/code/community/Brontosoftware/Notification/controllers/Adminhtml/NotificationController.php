<?php

class Brontosoftware_Notification_Adminhtml_NotificationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @see parent
     */
    public function preDispatch()
    {
        return Mage_Core_Controller_Varien_Action::preDispatch();
    }

    /**
     * Redirects the user to the appropriate link and marks as read
     */
    public function redirectAction()
    {
        $id = $this->getRequest()->getParam('id', false);
        $url = $this->getRequest()->getParam('url', false);
        if ($id && $url) {
            $redirectUrl = $this->_decrypt($url);
            $notificationId = $this->_decrypt($id);
            Mage::getModel('brontosoftware_connector/impl_core_inbox')->markAsRead($notificationId);
            $this->_redirectUrl($redirectUrl);
        } else {
            $this->_redirect('/');
        }
    }

    /**
     * @see parent
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Unwind the param values
     *
     * @param string $message
     * @return string
     */
    protected function _decrypt($message)
    {
        $encrypt = Mage::getModel('core/encryption');
        return $encrypt->decrypt(base64_decode(rawurldecode($message)));
    }
}
