<?php

class Brontosoftware_Connector_Model_Impl_Core_Currency implements Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface
{
    protected $_currencies = array();

    /**
     * @see parent
     */
    public function getByCode($code)
    {
        if (!array_key_exists($code, $this->_currencies)) {
            $this->_currencies[$code] = Mage::getModel('directory/currency')->load($code);
        }
        return $this->_currencies[$code];
    }
}
