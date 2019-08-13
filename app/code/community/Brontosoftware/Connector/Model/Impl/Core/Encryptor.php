<?php

class Brontosoftware_Connector_Model_Impl_Core_Encryptor implements Brontosoftware_Magento_Core_EncryptorInterface
{
    /**
     * @see parent
     */
    public function encrypt($message)
    {
        return Mage::getSingleton('core/encryption')->encrypt($message);
    }

    /**
     * @see parent
     */
    public function decrypt($message)
    {
        return Mage::getSingleton('core/encryption')->decrypt($message);
    }
}
