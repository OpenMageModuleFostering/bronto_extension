<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Order/Event/Source.php
 */

class Brontosoftware_Magento_Order_Event_Source extends Brontosoftware_Magento_Order_Event_CartBasedSourceAbstract
{
    /**
     * @see parent
     */
    public function create($object)
    {
        return array(
            'tid' => $this->_readCookie($object),
            'uniqueKey' => implode('.', array(
                $this->getEventType(),
                $this->action($object),
                $object->getId()
            ))
        );
    }

    /**
     * @see parent
     */
    public function getEventType()
    {
        return 'order';
    }

    /**
     * @see parent
     */
    public function action($order)
    {
        $orderService = $this->_connector->isOrderService('store', $order->getStoreId());
        $imports = $this->_helper->getImportStatus('store', $order->getStoreId());
        $deletes = $this->_helper->getDeleteStatus('store', $order->getStoreId());
        if (in_array($order->getStatus(), $imports)) {
            return self::ADD_ACTION;
        } else if (in_array($order->getStatus(), $deletes)) {
            return self::DELETE_ACTION;
        } else if ($orderService && $order->getStatus() == 'pending') {
            return self::ADD_ACTION;
        }
        return '';
    }

    /**
     * @see parent
     */
    protected function _initializeData($order, $isBase)
    {
        $data = array(
            'emailAddress' => $order->getCustomerEmail(),
            'customerOrderId' => $order->getIncrementId(),
            'status' => $this->_helper->getOrderStatus('store', $order->getStoreId()),
            'orderDate' => date('c', strtotime($order->getCreatedAt())),
            'currency' => $isBase ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode(),
        );
        $data['shippingAmount'] = $isBase ? $order->getBaseShippingAmount() : $order->getShippingAmount();
        if ($order->hasShipments()) {
            foreach ($order->getTracksCollection() as $track) {
                $data['shippingDate'] = date('c', strtotime($track->getCreatedAt()));
                $data['shippingDetails'] = array();
                if ($track->getTitle()) {
                    $data['shippingDetails'][] = $track->getTitle();
                }
                if ($track->getNumber()) {
                    $data['shippingDetails'][] = $track->getNumber();
                }
                $data['shippingDetails'] = implode(': ', $data['shippingDetails']);
                if ($track->getUrl()) {
                    $data['shippingTrackingUrl'] = $track->getUrl();
                }
            }
        }
        return $data;
    }
}
