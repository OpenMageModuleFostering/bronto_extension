<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Model_System_Config_Backend_Token extends Mage_Core_Model_Config_Data
{

    protected $_eventPrefix = 'bronto_token_model';

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
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('bronto_common')->__(
                    'You have changed your Bronto API Token so all Bronto modules have been disabled for this configuration scope.' .
                    '<br />Please proceed to each module and reconfigure all available options to avoid undesired behavior.'
                ));

                // reset the verified status
                Mage::helper('bronto_verify/roundtrip')->setStatus(
                    Mage::helper('bronto_verify/roundtrip')->getPath('status'),
                    '2',
                    $this->getScope(),
                    $this->getScopeId()
                );

                $sentry = Mage::getModel('bronto_common/keysentry');
                $sentry->disableModules($this->getScope(), $this->getScopeId());

                if (!Mage::helper('bronto_common')->isVersionMatch(Mage::getVersionInfo(), 1, 9)) {
                    $sentry->unlinkEmails(
                        Mage::getModel('bronto_email/message')->getCollection(),
                        $this->getScope(),
                        $this->getScopeId()
                    );
                }
            }
        } else {
            Mage::helper('bronto_verify/roundtrip')->setStatus(
                Mage::helper('bronto_verify/roundtrip')->getPath('status'),
                '2'
            );
        }

        return parent::_beforeSave();
    }
}
