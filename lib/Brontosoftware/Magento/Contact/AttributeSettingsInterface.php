<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Contact/AttributeSettingsInterface.php
 */

interface Brontosoftware_Magento_Contact_AttributeSettingsInterface
{
    /**
     * Returns a hash map of the extra field settings
     *
     * @return array
     */
    public function getFields();

    /**
     * Returns a hash map of the extra field values
     *
     * @param mixed $contact
     * @param mixed $storeId
     * @return array
     */
    public function getExtra($contact, $storeId = null);
}