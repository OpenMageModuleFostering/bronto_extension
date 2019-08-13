<?php

class Brontosoftware_Coupon_CouponController extends Mage_Core_Controller_Front_Action
{
    /**
     * Forwards to the rediect controller
     */
    public function indexAction()
    {
        $existingParams = $this->getRequest()->getParams();
        $existingParams['service'] = 'coupon';
        $this->_forward('index', 'redirect', 'brontosoftware_connector', $existingParams);
    }
}
