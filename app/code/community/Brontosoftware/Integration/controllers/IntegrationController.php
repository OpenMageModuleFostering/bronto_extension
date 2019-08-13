<?php

class Brontosoftware_Integration_IntegrationController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handles popup submissions
     */
    public function popupAction()
    {
        $emailAddress = $this->getRequest()->getParam('emailAddress');
        try {
            $integration = Mage::getSingleton('brontosoftware_integration/settings');
            $subscribers = Mage::getSingleton('brontosoftware_connector/impl_core_subscriber');
            $currentStore = Mage::app()->getStore();
            if ($integration->isCreateSubscribers('store', $currentStore)) {
                // Ignore status in event handling downstream
                $subscribers->subscribe($emailAddress, true);
            }
        } catch (Exception $e) {
            Mage::getSingleton('brontosoftware_connector/impl_core_logger')->critical($e);
        }
    }
}
