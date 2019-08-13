<?php

class Brontosoftware_Rating_Model_Observer extends Brontosoftware_Magento_Rating_CatalogMapper
{
    /**
     * @see parent
     */
    public function __construct()
    {
        parent::__construct(Mage::getSingleton('brontosoftware_rating/manager'));
    }
}
