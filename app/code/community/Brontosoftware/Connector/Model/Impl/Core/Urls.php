<?php

class Brontosoftware_Connector_Model_Impl_Core_Urls implements Brontosoftware_Magento_Core_Store_UrlManagerInterface
{
    /**
     * @see parent
     */
    public function getFrontendUrl($store, $path, $params = array())
    {
        return $store->getUrl($path, $params);
    }
}
