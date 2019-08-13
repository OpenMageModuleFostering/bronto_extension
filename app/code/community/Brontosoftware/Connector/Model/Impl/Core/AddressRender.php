<?php

class Brontosoftware_Connector_Model_Impl_Core_AddressRender implements Brontosoftware_Magento_Core_Sales_AddressRenderInterface
{
    /**
     * @see parent
     */
    public function format($address, $type)
    {
        return $address->format($type);
    }
}
