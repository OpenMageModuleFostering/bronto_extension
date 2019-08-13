<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Coupon/SettingsInterface.php
 */

interface Brontosoftware_Magento_Coupon_SettingsInterface extends Brontosoftware_Magento_Connector_Event_HelperInterface
{
    const XML_PATH_ENABLED = 'brontosoftware/coupon/extensions/settings/enabled';
    const XML_PATH_MESSAGE = 'brontosoftware/coupon/extensions/settings/%s_message';
    const XML_PATH_LINK_CONTENT = 'brontosoftware/coupon/extensions/settings/link_text';
    const XML_PATH_COUPON_PARAM = 'brontosoftware/coupon/extensions/settings/coupon_param';
    const XML_PATH_INVALID_PARAM = 'brontosoftware/coupon/extensions/settings/invalid_param';
    const INVALID_CODE = 'invalid';
    const DEPLETED_CODE = 'depleted';
    const EXPIRED_CODE = 'expired';
    const CONFLICT_CODE = 'conflict';
    const FORCE_PARAM = '___force_code';

    /**
     * Is this request a forced application
     *
     * @return boolean
     */
    public function isForced();

    /**
     * Get the params for the store
     *
     * @param string $scopeType
     * @param mixed $scopeId
     * @return array
     */
    public function getParams($scopeType = 'default', $scopeId = null);

    /**
     * Determines if the extension should display a message
     *
     * @param string $scopeType
     * @param mixed $scopeId
     * @return boolean
     */
    public function isDisplayMessage($scopeType = 'default', $scopeId = null);

    /**
     * Gets the link content in case of a conflict
     *
     * @param string $scopeType
     * @param mixed $scopeId
     * @return string
     */
    public function getLinkContent($scopeType = 'default', $scopeId = null);

    /**
     * Applies the coupon code from a request
     *
     * @param mixed $messages
     * @param mixed $store
     * @return boolean
     */
    public function applyCodeFromRequest($messages, $store);

    /**
     * Applies the coupon code from a rule or session
     *
     * @param mixed $ruleId
     * @param string $couponCode
     * @return void
     */
    public function applyCode($ruleId = null, $couponCode = null);
}