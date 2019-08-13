<?php

class Brontosoftware_Rating_Model_Manager implements Brontosoftware_Magento_Product_CatalogMapperManagerInterface
{
    protected $_caches = array();

    /**
     * @see parent
     */
    public function getByProduct($product)
    {
        if (!array_key_exists($product->getId(), $this->_caches)) {
            $report = Mage::getResourceModel('reports/review_product_collection');
            $report->joinReview()->addFieldToFilter('entity_id', array('eq' => $product->getId()));
            $this->_caches[$product->getId()] = new Brontosoftware_Magento_Core_DataObject(array(
                'review_cnt' => 0,
                'avg_rating' => 0,
                'avg_rating_approved' => 0,
                'last_created' => null
            ));
            foreach ($report as $entry) {
                $this->_caches[$product->getId()] = $entry;
                break;
            }
        }
        return $this->_caches[$product->getId()];
    }
}
