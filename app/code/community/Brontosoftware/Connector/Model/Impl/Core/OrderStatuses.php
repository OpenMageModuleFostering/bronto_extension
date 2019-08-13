<?php

class Brontosoftware_Connector_Model_Impl_Core_OrderStatuses implements Brontosoftware_Magento_Core_Sales_OrderStatusesInterface
{
    protected $_statuses;

    /**
     * @see parent
     */
    public function getOptionArray()
    {
        if (is_null($this->_statuses)) {
            $states = Mage::getModel('sales/order_config')->getStatuses();
            $this->_statuses = array();
            foreach ($states as $value => $label) {
                $this->_statuses[] = array(
                    'id' => $value,
                    'name' => $label
                );
            }
        }
        return $this->_statuses;
    }
}
