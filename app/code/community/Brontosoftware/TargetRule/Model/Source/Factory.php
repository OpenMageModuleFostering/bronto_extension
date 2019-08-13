<?php

class Brontosoftware_TargetRule_Model_Source_Factory extends Brontosoftware_Recommendation_Model_Source_Factory
{
    /**
     * @see parent
     */
    public function create($source, array $promotion, array $context = array())
    {
        $childSource = parent::create($source, $promotion, $context);
        if (array_key_exists('product', $context) && Mage::getModel('enterprise_targetrule/rule')) {
            $childSource = new Brontosoftware_Magento_TargetRule_Source_Rule(
                $promotion,
                $context['product'],
                $source,
                $childSource,
                Mage::getSingleton('brontosoftware_targetrule/index'),
                Mage::helper('brontosoftware_targetrule'));
        }
        return $childSource;
    }
}
