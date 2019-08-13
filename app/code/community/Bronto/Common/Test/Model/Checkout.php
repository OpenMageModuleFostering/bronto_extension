<?php

abstract class Bronto_Common_Test_Model_Checkout extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @return Mage_Sales_Model_Order
     */
    public function createRandomGuestOrder()
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_queryOrder();

        /* @var $service Mage_Sales_Model_Service_Quote */
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        $order = $service->getOrder();
        $order->sendNewOrderEmail();
        return $order;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function createInvoiceForOrder(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            $invoiceId = Mage::getModel('sales/order_invoice_api')
                ->create($order->getIncrementId(), array());

            return Mage::getModel('sales/order_invoice')
                ->loadByIncrementId($invoiceId)
                ->capture()
                ->save();
        }

        return false;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function createShipmentForOrder(Mage_Sales_Model_Order $order)
    {
        if ($order->canShip()) {
            $shipmentId = Mage::getModel('sales/order_shipment_api')
                ->create($order->getIncrementId(), array(), 'Test Shipment Created', true);

            return Mage::getModel('sales/order_shipment_api')
                ->addTrack($shipmentId, 'ups', 'UPS Test Shipment', rand(1000000000, 9999999999));
        }

        return false;
    }

    /**
     * @param array $orderData
     * @return Mage_Sales_Model_Quote
     */
    protected function _queryOrder()
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote');

        foreach (array(166, 156, 149) as $productId) {
            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product')->load($productId);
            $quote->addProduct($product, 1);
        }

        $this->_addBillingAddress($quote);
        $this->_addShippingAddress($quote);
        $this->_addShippingMethod($quote);
        $this->_addPayment($quote);

        $quote = $this->_prepareGuestQuote($quote);
        $quote->collectTotals()->save();
        return $quote;
    }

    /**
     * @param string $regionCode
     * @param string $countryCode
     * @return null
     */
    protected function _getIdByRegionCode($regionCode, $countryCode)
    {
        $region = Mage::getModel('directory/region')->loadByCode($regionCode, $countryCode);
        if (!!$region && !!$region->getId()) {
            return $region->getId();
        }
        return null;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _addBillingAddress(Mage_Sales_Model_Quote $quote)
    {
        $billingAddress = $quote->getBillingAddress();
        $billingAddress
            ->setData('firstname', 'John')
            ->setData('lastname', 'Doe ' . rand(0, 99))
            ->setData('street', '123 Main St')
            ->setData('city', 'Monroe')
            ->setData('postcode', '28110')
            ->setData('region_id', $this->_getIdByRegionCode('NC', 'US'))
            ->setData('region', 'NC')
            ->setData('country_id', 'US')
            ->setData('telephone', '7045555555');
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _addShippingAddress(Mage_Sales_Model_Quote $quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress
            ->setData('firstname', 'John')
            ->setData('lastname', 'Doe ' . rand(0, 99))
            ->setData('street', '123 Main St')
            ->setData('city', 'Monroe')
            ->setData('postcode', '28110')
            ->setData('region_id', $this->_getIdByRegionCode('NC', 'US'))
            ->setData('region', 'NC')
            ->setData('country_id', 'US')
            ->setData('telephone', '7045555555');
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _addShippingMethod(Mage_Sales_Model_Quote $quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setShippingMethod('flatrate_flatrate');
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _addPayment(Mage_Sales_Model_Quote $quote)
    {
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod('checkmo');
        } else {
            $quote->getShippingAddress()->setPaymentMethod('checkmo');
        }

        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        /* @var $payment Mage_Sales_Model_Quote_Payment */
        $payment = $quote->getPayment();
        $payment->importData(array('method' => 'checkmo'));
        $quote->setPayment($payment);
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Quote
     */
    protected function _prepareGuestQuote(Mage_Sales_Model_Quote $quote)
    {
        $quote->setCustomerId(null);
        $quote->setCustomerEmail('j.doe+' . date('YmdHis') . '@bronto.com');
        $quote->setCustomerIsGuest(true);
        $quote->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        return $quote;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param type $customerId
     * @return Mage_Sales_Model_Quote
     */
    protected function _prepareCustomerQuote(Mage_Sales_Model_Quote $quote, $customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $quote->setCustomer($customer);
        return $quote;
    }
}
