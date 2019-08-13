<?php

class Brontosoftware_Integration_Model_Settings extends Brontosoftware_Magento_Integration_Settings
{
    /**
     * Override for DI
     */
    public function __construct()
    {
        $scoped = Mage::getSingleton('brontosoftware_connector/impl_core_scoped');
        $cartSettings = Mage::getSingleton('brontosoftware_cart/settings');
        if (empty($cartSettings)) {
            $cartSettings = new Brontosoftware_Magento_Integration_CartSettings(
                Mage::getSingleton('brontosoftware_connector/impl_core_encryptor'),
                Mage::getSingleton('brontosoftware_connector/impl_core_cookies'),
                $scoped,
                Mage::getSingleton('brontosoftware_connector/impl_core_urls')
            );
        }
        $couponSettings = Mage::getSingleton('brontosoftware_redemption/settings');
        if (empty($couponSettings)) {
            $couponSettings = new Brontosoftware_Magento_Integration_CouponSettings($scoped);
        }
        $popupSettings = new Brontosoftware_Magento_Integration_PopupSettings($scoped);
        parent::__construct(
            $cartSettings,
            $popupSettings,
            $couponSettings);
    }
}
