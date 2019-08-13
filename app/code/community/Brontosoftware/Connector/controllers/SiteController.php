<?php

class Brontosoftware_Connector_SiteController extends Mage_Core_Controller_Front_Action
{
    const FIDDLE_EVENT = 'brontosoftware_site_fiddle';

    /**
     * Action that handles all Bronto related fiddles
     */
    public function fiddleAction()
    {
        Mage::dispatchEvent(self::FIDDLE_EVENT, array(
            'request' => $this->getRequest()
        ));
    }
}
