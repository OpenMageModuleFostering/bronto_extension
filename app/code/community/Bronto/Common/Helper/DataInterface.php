<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
interface Bronto_Common_Helper_DataInterface
{
    /**
     * Disable the module in the admin configuration
     *
     * @param string $scope
     * @param int $scopeId
     * @return bool
     */
    public function disableModule($scope = 'default', $scopeId = 0);
}
