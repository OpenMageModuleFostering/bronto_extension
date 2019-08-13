<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Connector/RegistrationManagerInterface.php
 */

interface Brontosoftware_Magento_Connector_RegistrationManagerInterface
{
    /**
     * Gets the registration by scope and scopeId
     *
     * @param string $scope
     * @param mixed $scopeId
     * @return Brontosoftware_Magento_Connector_RegistrationInterface
     */
    public function getByScope($scope, $scopeId);

    /**
     * Gets all of the registrations on the platform
     *
     * @return mixed
     */
    public function getAll();
}