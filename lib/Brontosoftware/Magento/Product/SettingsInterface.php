<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Product/SettingsInterface.php
 */

interface Brontosoftware_Magento_Product_SettingsInterface extends Brontosoftware_Magento_Connector_Event_HelperInterface
{
    const XML_PATH_ENABLED = 'brontosoftware/product/extensions/settings/enabled';
    const XML_PATH_ADD_LINK = 'brontosoftware/product/extensions/settings/addProductsLink';
    const XML_PATH_SCOPES = 'brontosoftware/product/extensions/scopes/%';
    const XML_PATH_DEFAULTS = 'brontosoftware/product/extensions/default_fields';
    const XML_PATH_CUSTOMS = 'brontosoftware/product/objects/custom_fields';

    /**
     * Determines if products are configured to be added via URL
     *
     * @param string $scope
     * @param mixed $scopeId
     * @return boolean
     */
    public function isProductAddLink($scope = 'default', $scopeId = null);

    /**
     * Gets the object mapping for the provided scope
     *
     * @param mixed $product
     * @return array
     */
    public function getFieldMapping($product);

    /**
     * Gets the display options for the default mappings
     *
     * @param Brontosoftware_Magento_Connector_RegistrationInterface $registration
     * @return array
     */
    public function getDefaultFields(Brontosoftware_Magento_Connector_RegistrationInterface $registration);

    /**
     * Gets the display options for the custom mappings
     *
     * @param Brontosoftware_Magento_Connector_RegistrationInterface $registration
     * @return array
     */
    public function getCustomFields(Brontosoftware_Magento_Connector_RegistrationInterface $registration);

    /**
     * Gets a list of attributes for Catalog updating
     *
     * @param Brontosoftware_Magento_Connector_RegistrationInterface $registration
     * @return array
     */
    public function getFieldAttributes(Brontosoftware_Magento_Connector_RegistrationInterface $registration);

    /**
     * Gets all of the configured mappings by scope heirarchy
     *
     * @param mixed $scopeId
     * @return array
     */
    public function getAll($storeId = null);

    /**
     * Gets all of the enabled scopes from the settings
     *
     * @param string $scope
     * @param mixed $scopeId
     * @return array
     */
    public function getEnabledStores($scope = 'default', $scopeId = null);
}
