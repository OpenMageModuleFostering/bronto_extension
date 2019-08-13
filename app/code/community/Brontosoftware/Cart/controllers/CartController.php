<?php

class Brontosoftware_Cart_CartController extends Mage_Core_Controller_Front_Action
{
    /**
     * Encrypts / encodes the email address appropriately
     */
    public function captureAction()
    {
        $emailAddress = $this->getRequest()->getParam('emailAddress', null);
        if (Zend_Validate::is($emailAddress, 'EmailAddress')) {
            try {
                Mage::getSingleton('brontosoftware_cart/settings')->setCartRecoveryCookie($emailAddress);
            } catch (Exception $e) {
                Mage::getSingleton('brontosoftware_connector/impl_core_logger')->critical($e);
            }
        }
    }
}
