<?php

class Brontosoftware_TargetRule_Model_Index implements Brontosoftware_Magento_TargetRule_IndexFactoryInterface
{
    /**
     * @see parent
     */
    public function create()
    {
        return Mage::getModel('enterprise_targetrule/index');
    }
}
