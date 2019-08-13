<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Model_System_Config_Backend_Token extends Mage_Core_Model_Config_Data
{
    /**
     * @return Bronto_Common_Model_System_Config_Backend_Token
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (!empty($value)) {
            if (Mage::helper('bronto_common')->validApiToken($value) === false) {
                Mage::throwException(Mage::helper('bronto_common')->__('The Bronto API Token you have entered appears to be invalid.'));
            }

            //  API key is new and doesn't match existing API key
            $currentApiKey = Mage::helper('bronto_common')->getApiToken();
            if ($currentApiKey !== $value) {
                // reset the verified status
                Mage::helper('bronto_roundtrip')->setRoundtripStatus(
                    Mage::helper('bronto_roundtrip')->getPath('status'),
                    '2',
                    $this->getScope(),
                    $this->getScopeId()
                );

                $sentry = Mage::getModel('bronto_common/keysentry');
                $sentry->disableModules($this->getScope(), $this->getScopeId());
                $version = Mage::getVersionInfo();
                if (1 == $version['major'] && 9 != $version['minor']) {
                    $sentry->unlinkEmails(
                        Mage::getResourceSingleton('core/email_template_collection'),
                        $this->getScopeId()
                    );
                }
            }
        } else {
            Mage::helper('bronto_roundtrip')->setRoundtripStatus(
                Mage::helper('bronto_roundtrip')->getPath('status'),
                '2'
            );
        }

        return parent::_beforeSave();
    }
}
