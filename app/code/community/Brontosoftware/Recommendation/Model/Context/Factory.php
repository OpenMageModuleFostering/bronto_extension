<?php

class Brontosoftware_Recommendation_Model_Context_Factory implements Brontosoftware_Magento_Recommendation_Collect_ContextFactoryInterface
{
    protected static $typeToModel = array(
        Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_ORDER => 'sales/order',
        Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_CART => 'sales/quote',
        Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_WISHLIST => 'wishlist/wishlist',
        Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_SHIPMENT => 'sales/order_shipment',
        Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_INVOICE => 'sales/order_invoice'
      );

    /**
     * @see parent
     */
    public function create($object)
    {
        $contextType = Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_EMPTY;
        $params = array();
        if ($object instanceof Mage_Sales_Model_Order) {
            $contextType = Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_ORDER;
            $params['item'] = $object;
        } else if ($object instanceof Mage_Sales_Model_Quote) {
            $contextType = Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_CART;
            $params['item'] = $object;
        } else if ($object instanceof Mage_Wishlist_Model_Wishlist) {
            $contextType = Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_WISHLIST;
            $params[$contextType] = $object;
        } else if ($object instanceof Mage_Sales_Model_Order_Invoice) {
            $contextType = Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_INVOICE;
            $params[$contextType] = $object;
        } else if ($object instanceof Mage_Sales_Model_Order_Shipment) {
            $contextType = Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_SHIPMENT;
            $params[$contextType] = $object;
        } else if (is_array($object)) {
            $contextType = Brontosoftware_Magento_Recommendation_Collect_ContextInterface::TYPE_CUSTOM;
            $params = $object;
        }
        $contextClassName = 'Brontosoftware_Magento_Recommendation_Collect_Context_' . ucfirst($contextType);
        $class = new ReflectionClass($contextClassName);
        return $class->newInstanceArgs($params);
    }

    /**
     * @see parent
     */
    public function get($contextType, $modelId)
    {
        $model = null;
        if (!is_numeric($modelId)) {
            $model = $modelId;
        } else {
            if (array_key_exists($contextType, self::$typeToModel)) {
                $modelClass = self::$typeToModel[$contextType];
                $model = Mage::getModel($modelClass)->load($modelId);
                if (!$model->getId()) {
                    $model = null;
                }
            }
        }
        return $this->create($model);
    }
}
