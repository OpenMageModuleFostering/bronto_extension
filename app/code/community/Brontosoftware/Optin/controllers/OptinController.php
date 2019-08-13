<?php

class Brontosoftware_Optin_OptinController extends Mage_Core_Controller_Front_Action
{
    /**
     * Optins the user
     */
    public function checkoutAction()
    {
        $subscribed = (bool) $this->getRequest()->getParam('subscribed', 0);
        if ($subscribed) {
            Mage::getSingleton('checkout/session')->setSubscribeToNewsletter(true);
        } else {
            Mage::getSingleton('checkout/session')->unsSubscribeToNewsletter();
        }
    }
}
