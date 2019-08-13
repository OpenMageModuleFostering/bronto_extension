<?php

class Brontosoftware_Connector_Model_Impl_Core_Cookies implements Brontosoftware_Magento_Core_Cookie_ReaderInterface, Brontosoftware_Magento_Core_Cookie_WriterInterface
{
    /**
     * @see parent
     */
    public function getCookie($name, $defaultValue)
    {
        foreach (Mage::getModel('core/cookie')->get() as $key => $value) {
            if ($key == $name) {
                return $value;
            }
        }
        return $defaultValue;
    }

    /**
     * @see parent
     */
    public function setServerCookie($name, $value, array $metadata = array())
    {
        $cookie = Mage::getModel('core/cookie');
        $cookie->set(
            $name,
            $value,
            $this->_extract($metadata, 'duration', true),
            $this->_extract($metadata, 'path', null),
            $this->_extract($metadata, 'domain', null),
            $this->_extract($metadata, 'secure', null),
            $this->_extract($metadata, 'httponly', true));
    }

    /**
     * Properly extract the values from the metadata into useful things
     *
     * @param array $metadata
     * @param string $keyName
     * @param string $defaultValue
     */
    protected function _extract($metadata, $keyName, $defaultValue)
    {
        if (array_key_exists($keyName, $metadata)) {
            return $metadata[$keyName];
        } else {
            $defaultValue;
        }
    }
}
