<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Integration/CouponSettingsInterface.php
 */

interface Brontosoftware_Magento_Integration_CouponSettingsInterface
{
    const XML_PATH_COUPON_ENABLED = 'brontosoftware/integration/extensions/coupon_manager/enabled';

    /**
     * Is Coupon module enabled?
     *
     * @param string $scopeType
     * @param mixed $scopeId
     * @return boolean
     */
    public function isCouponEnabled($scopeType = 'default', $scopeId = null);
}