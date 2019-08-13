<?php

/**
 * @package   Bronto\Reviews
 * @copyright 2011-2013 Bronto Software, Inc.
 */
class Bronto_Reviews_Model_Observer
{
    const NOTICE_IDENTIFER = 'bronto_reviews';

    // Helper
    protected $_helper;

    // Data Members
    protected $_contact;
    protected $_order;
    protected $_deliveryObject;
    protected $_deliveryRow;
    protected $_deliveryId;

    public function __construct()
    {
        /* @var Bronto_Reviews_Helper_Data $_helper */
        $this->_helper = Mage::helper(self::NOTICE_IDENTIFER);
    }

    /**
     * Set Contact Row Object to use
     *
     * @param Bronto_Api_Contact_Row $contact
     *
     * @return Bronto_Reviews_Model_Observer
     */
    public function setContact(Bronto_Api_Contact_Row $contact)
    {
        $this->_contact = $contact;

        return $this;
    }

    /**
     * Get Contact Row Object to use
     *
     * @return Bronto_Api_Contact_Row
     */
    public function getContact()
    {
        if (!$this->_contact) {
            // Retrieve Store's configured API Token
            $token = $this->_helper->getApiToken('store', $this->getOrder()->getStoreId());

            /** @var Bronto_Common_Model_Api $api */
            $api = $this->_helper->getApi($token, 'store', $this->getOrder()->getStoreId());

            /** @var Bronto_Api_Contact $contactObject */
            $contactObject = $api->getContactObject();

            /** @var Bronto_Api_Contact_Row $brontoContact */
            $brontoContact        = $contactObject->createRow(array());
            $brontoContact->email = $this->getOrder()->getCustomerEmail();
            $brontoContact->save();

            $this->setContact($brontoContact);
        }

        return $this->_contact;
    }

    /**
     * Set Order to use
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Bronto_Reviews_Model_Observer
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;

        return $this;
    }

    /**
     * Get Order to use
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Set Delivery Object to use
     *
     * @param Bronto_Api_Delivery $deliveryObject
     *
     * @return Bronto_Reviews_Model_Observer
     */
    public function setDeliveryObject(Bronto_Api_Delivery $deliveryObject)
    {
        $this->_deliveryObject = $deliveryObject;

        return $this;
    }

    /**
     * Get Delivery Object to use
     *
     * @return boolean|Bronto_Api_Delivery
     */
    public function getDeliveryObject()
    {
        if (!$this->_deliveryObject) {
            try {
                // Retrieve Store's configured API Token
                $token = $this->_helper->getApiToken('store', $this->getOrder()->getStoreId());

                /* @var Bronto_Common_Model_Api $api */
                $api = $this->_helper->getApi($token, 'store', $this->getOrder()->getStoreId());

                /* @var Bronto_Api_Delivery $deliveryObject */
                $this->_deliveryObject = $api->getDeliveryObject();
            } catch (Exception $e) {
                $this->_helper->writeError('Bronto Failed creating apiObject:' . $e->getMessage());

                return false;
            }
        }

        return $this->_deliveryObject;
    }

    /**
     * Set Delivery Row Object to use
     *
     * @param Bronto_Api_Delivery_Row $deliveryRow
     *
     * @return Bronto_Reviews_Model_Observer
     */
    public function setDeliveryRow(Bronto_Api_Delivery_Row $deliveryRow)
    {
        $this->_deliveryRow = $deliveryRow;

        return $this;
    }

    /**
     * Get Delivery Row if exists, create if doesn't
     *
     * @return boolean
     */
    public function getDeliveryRow()
    {
        if (!$this->_deliveryRow) {
            try {
                $this->_deliveryRow = $this->getDeliveryObject()->createRow(array());
            } catch (Exception $e) {
                $this->_helper->writeError('Bronto Failed creating apiObject:' . $e->getMessage());

                return false;
            }
        }

        return $this->_deliveryRow;
    }

    /**
     * Set Delivery ID
     *
     * @param string $deliveryId
     *
     * @return Bronto_Reviews_Model_Observer
     */
    public function setDeliveryId($deliveryId)
    {
        $this->_deliveryId = $deliveryId;

        return $this;
    }

    /**
     * Get Delivery ID
     *
     * @return string
     */
    public function getDeliveryId()
    {
        return $this->_deliveryId;
    }

    /**
     * Observe saving of Order and determine if a Review Request should be sent
     * and then send
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function markOrderForReview(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled('store', Mage::app()->getStore()->getId())) {
            return $observer;
        }

        $this->setOrder($observer->getOrder())->process();

        return $observer;
    }

    /**
     * Process Order for Review Request
     */
    public function process()
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $this->getOrder();

        // Get Statuses
        $reviewStatus = $this->_helper->getReviewSendStatus('store', $order->getStoreId());
        $cancelStatus = $this->_helper->getReviewCancelStatus('store', $order->getStoreId());

        // If Original Increment ID is Null, proceed
        if (is_null($order->getOriginalIncrementId())) {
            // If current order status matches review send status, proceed
            if ($order->getStatus() == $reviewStatus) {
                $reviewQueue = Mage::getModel('bronto_reviews/queue')
                    ->load($order->getId());

                // If Queue Doesn't have Delivery ID, proceed
                if (is_null($reviewQueue->getDeliveryId())) {
                    $this->_makeDelivery();

                    // If Delivery Row sent correctly, save the ID
                    if ($this->getDeliveryId()) {
                        $reviewQueue->setDeliveryId($this->getDeliveryId())->save();
                    }
                }
            } elseif (in_array($order->getStatus(), $cancelStatus)) {
                $reviewQueue = Mage::getModel('bronto_reviews/queue')
                    ->load($order->getId());

                // If Queue has Delivery ID, cancel Delivery
                if (!is_null($reviewQueue->getDeliveryId())) {
                    $this->_cancelDelivery($reviewQueue->getDeliveryId());
                }
            }
        }
    }

    /**
     * Deletes the Delivery that was previously created
     *
     * @param $deliveryId
     */
    protected  function _cancelDelivery($deliveryId)
    {
        try {
            $result = $this->getDeliveryObject()->delete(array('id' => $deliveryId));
            if ($result->hasErrors()) {
                $error = implode('<br />', $result->getErrors());

                Mage::throwException($error);
            }
        } catch (Exception $e) {
            $this->_helper->writeError('Failed Cancelling Delivery: ' . $e->getMessage());
        }
    }

    /**
     * Create Delivery With Order Details
     */
    protected function _makeDelivery()
    {
        try {
            // Get Delivery Object
            $this->_helper->writeDebug('    Creating Delivery Row');

            /** @var $deliveryRow Bronto_Api_Delivery_Row */
            $deliveryRow = $this->getDeliveryRow();

            // Get Order Object
            /** @var $order Mage_Sales_Model_Order */
            $order = $this->getOrder();

            // Get Contact Object
            $this->_helper->writeDebug('    Creating Contact Object for email: ' . $order->getCustomerEmail());
            /** @var $contact Bronto_Api_Contact_Row */
            $contact = $this->getContact();

            // Create Recipient
            $deliveryRecipientObject = array(
                'type' => 'contact',
                'id'   => $contact->id
            );

            // Create Send Time
            $sendTime = date('c', strtotime('+' . abs($this->_helper->getReviewSendPeriod('store', $order->getStoreId())) . ' days'));
            $this->_helper->writeDebug('    Delivery being set for ' . $sendTime);

            // Create Delivery Row
            $deliveryRow->start      = $sendTime;
            $deliveryRow->messageId  = $this->_helper->getReviewSendMessage('store', $order->getStoreId());
            $deliveryRow->type       = 'marketing';
            $deliveryRow->fromEmail  = $this->_helper->getReviewSenderEmail('store', $order->getStoreId());
            $deliveryRow->fromName   = $this->_helper->getReviewSenderName('store', $order->getStoreId());
            $deliveryRow->replyEmail = $this->_helper->getReviewReplyTo('store', $order->getStoreId());
            $deliveryRow->recipients = array($deliveryRecipientObject);
            $deliveryRow->fields     = $this->_buildFields();

            // Save Delivery
            $this->_helper->writeDebug('    Saving Delivery Row');
            $deliveryRow->save();

            // Verbose Logging
            $this->_helper->writeVerboseDebug('===== FLUSH =====', 'bronto_reviews_api.log');
            $this->_helper->writeVerboseDebug(var_export($this->getDeliveryObject()->getApi()->getLastRequest(), true), 'bronto_reviews_api.log');
            $this->_helper->writeVerboseDebug(var_export($this->getDeliveryObject()->getApi()->getLastResponse(), true), 'bronto_reviews_api.log');

            if ($deliveryRow->hasError()) {
                Mage::throwException($deliveryRow->getErrorCode() . ' ' . $deliveryRow->getErrorMessage());
            } else {
                $this->setDeliveryId($deliveryRow->id);
                $this->_helper->writeLog("Review Request sent to {$order->getCustomerEmail()}. Delivery ID: {$deliveryRow->id}");
            }
        } catch (Exception $e) {
            $this->_helper->writeError('Bronto Failed creating apiObject:' . $e->getMessage());
        }
    }

    /**
     * Get array of fields for delivery
     *
     * @return array
     */
    protected function _buildFields()
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = $this->getOrder();

        // Build Fields
        $fields = array(
            array('name' => 'orderCustomerName', 'type' => 'html', 'content' => $order->getCustomerName()),
            array('name' => 'orderIncrementId', 'type' => 'html', 'content' => $order->getIncrementId()),
            array('name' => 'orderCreatedAt', 'type' => 'html', 'content' => $order->getCreatedAt()),
        );

        // Cycle through order items and create fields
        $productInc = 1;
        foreach ($order->getAllVisibleItems() as $item) {
            // Get Store ID from Order
            $storeId    = $order->getStoreId();

            /** @var Mage_Catalog_Model_Product $product */
            $product    = Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getProductId());

            // Build Product URL with Suffix Config
            $productUrl = Mage::helper('bronto_order')->getItemUrl($item, $product, $storeId);
            $productUrl .= ltrim($this->_helper->getProductUrlSuffix('store', $storeId), '/');

            $reviewUrl = $this->_helper->getReviewsUrl($product, $storeId);
            $reviewUrl .= ltrim($this->_helper->getProductUrlSuffix('store', $storeId), '/');

            // Add Reviews Url
            $fields[] = array(
                'name' => 'reviewUrl_' . $productInc,
                'type' => 'html',
                'content' => $reviewUrl
            );

            // Add Product Name Field
            $fields[] = array(
                'name' => 'productName_' . $productInc,
                'type' => 'html',
                'content' => $item->getName()
            );

            // Add Product Image Field
            $fields[] = array(
                'name' => 'productImgUrl_' . $productInc,
                'type' => 'html',
                'content' => Mage::helper('bronto_order')->getItemImg($item, $product, $storeId)
            );

            // Add Product URL Field
            $fields[] = array(
                'name' => 'productUrl_' . $productInc,
                'type' => 'html',
                'content' => $productUrl
            );

            // Increment Count
            $productInc++;
        }

        return $fields;
    }
}
