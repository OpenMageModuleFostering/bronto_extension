<?php

class Brontosoftware_TargetRule_Helper_Data extends Mage_Core_Helper_Abstract implements Brontosoftware_Magento_TargetRule_SettingsInterface
{
    /**
     * @see parent
     */
    public function getShowProducts($type)
    {
        return Mage::helper('enterprise_targetrule')->getShowProducts($type);
    }

    /**
     * @see parent
     */
    public function getMaximumNumberOfProduct($type)
    {
        return Mage::helper('enterprise_targetrule')->getMaximumNumberOfProduct($type);
    }

    /**
     * @see parent
     */
    public function getMaxProductsListResult($number = 0)
    {
        return Mage::helper('enterprise_targetrule')->getMaxProductsListResult($number);
    }

    /**
     * @see parent
     */
    public function rotate($type, $productIds)
    {
        $mode = Mage::helper('enterprise_targetrule')->getRotationMode($type);
        if ($mode == Enterprise_TargetRule_Model_Rule::ROTATION_SHUFFLE) {
            shuffle($productIds);
        }
        return $productIds;
    }
}
