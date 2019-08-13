<?php

class Brontosoftware_Integration_Model_Observer extends Brontosoftware_Magento_Integration_ExtensionAbstract
{
    /**
     * @see parent
     */
    public function translate($message)
    {
        return Mage::Helper('brontosoftware_integration')->__($message);
    }
}
