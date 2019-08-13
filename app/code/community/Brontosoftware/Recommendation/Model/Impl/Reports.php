<?php

class Brontosoftware_Recommendation_Model_Impl_Reports implements Brontosoftware_Magento_Core_Report_ManagerInterface, Brontosoftware_Magento_Core_Report_CollectionFactoryInterface
{
    protected static $keyToCollection = array(
        Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_BESTSELLER => 'sales/report_bestsellers',
        Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_VIEWED => 'reports/report_product_viewed'
    );

    protected static $keyToFlagCode = array(
        Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_BESTSELLER => Mage_Reports_Model_Flag::REPORT_BESTSELLERS_FLAG_CODE,
        Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_VIEWED => Mage_Reports_Model_Flag::REPORT_PRODUCT_VIEWED_FLAG_CODE
    );

    /**
     * @see parent
     */
    public function getLastUpdate($reportKey)
    {
        $updatedAt = '';
        if ($this->isReportKey($reportKey)) {
            $flagCode = self::$keyToFlagCode[$reportKey];
            $flag = Mage::getModel('reports/flag')->setReportFlagCode($flagCode)->loadSelf();
            if ($flag->hasData()) {
                $updatedAt = $flag->getLastUpdate();
            }
        }
        return $updatedAt;
    }

    /**
     * @see parent
     */
    public function isReportKey($reportKey)
    {
        return array_key_exists($reportKey, self::$keyToFlagCode);
    }

    /**
     * @see parent
     */
    public function refresh($reportKey, $fromTime = null, $toTime = null)
    {
        if ($this->isReportKey($reportKey)) {
            $collectionName = self::$keyToCollection[$reportKey];
            Mage::getResourceModel($collectionName)->aggregate($fromTime, $toTime);
            return true;
        }
        return false;
    }

    /**
     * @see parent
     */
    public function getMostViewed()
    {
        $collectionName = self::$keyToCollection[Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_VIEWED] . '_collection';
        return Mage::getResourceModel($collectionName);
    }

    /**
     * @see parent
     */
    public function getBestSellers()
    {
        $collectionName = self::$keyToCollection[Brontosoftware_Magento_Recommendation_Collect_SourceInterface::TYPE_BESTSELLER] . '_collection';
        return Mage::getResourceModel($collectionName);
    }

    /**
     * @see parent
     */
    public function getNewProducts()
    {
        return Mage::getModel('catalog/product')->getCollection();
    }
}
