<?php

class Brontosoftware_Recommendation_Model_Source_Factory implements Brontosoftware_Magento_Recommendation_Collect_SourceFactoryInterface
{
    protected static $constructors = array();

    /**
     * @see parent
     */
    public function create($source, array $promotion, array $context = array())
    {
        $context['promotion'] = $promotion;
        switch ($source) {
        case Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_INITIAL:
            $context['sourceFactory'] = Mage::getSingleton('brontosoftware_recommendation/source_factory');
            $context['settings'] = Mage::getSingleton('brontosoftware_recommendation/settings');
            $context['productRepo'] = Mage::getSingleton('brontosoftware_connector/impl_core_productCacheBridge');
            $context['logger'] = Mage::getSingleton('brontosoftware_connector/impl_core_logger');
            break;
        case Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_MANUALCATGORY:
            $context['categoryRepo'] = Mage::getSingleton('brontosoftware_connector/impl_core_categoryCacheBridge');
            break;
        case Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_NEW:
        case Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_VIEWED:
        case Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_BESTSELLER:
            $context['factory'] = Mage::getSingleton('brontosoftware_recommendation/impl_reports');
            break;
        }
        if (!array_key_exists($source, self::$constructors)) {
            $sourcePrefix = 'Brontosoftware_Magento_Recommendation_Collect_Source_';
            $class = new ReflectionClass($sourcePrefix . ucfirst($source));
            self::$constructors[$source] = $class;
        }
        $constructor = self::$constructors[$source]->getConstructor();
        $params = array();
        foreach ($constructor->getParameters() as $param) {
            $value = null;
            if (array_key_exists($param->name, $context)) {
                $value = $context[$param->name];
            }
            $params[$param->name] = $value;
        }
        return self::$constructors[$source]->newInstanceArgs($params);
    }
}
