<?php

class Bronto_Email_Test_Model_Email_Message
    extends Bronto_Common_Test_Model_Checkout
{
    /**
     * @test
     */
    public function testOrderEmailSent()
    {
        $this->markTestIncomplete('need to mock/replace sessions before this will work.');
        $mockOrder = $this->getModelMock('sales/order', array('sendNewOrderEmail'));
        $mockOrder->expects($this->any())
              ->method('sendNewOrderEmail')
              ->will($this->returnCallback(array($this, '')));
        $this->replaceByMock('model', 'sales/order', $mockOrder);

        $order = $this->createRandomGuestOrder();

        $this->assertEventDispatchedExactly('bronto_email_send_before', 1);
        $this->assertEventDispatchedExactly('bronto_email_send_after', 1);

        return $order;
    }

    /**
     * @test
     * @depends testOrderEmailSent
     */
    public function testShipmentEmailSent(Mage_Sales_Model_Order $order)
    {
        $this->createShipmentForOrder($order);

        $this->assertEventDispatchedExactly('bronto_email_send_before', 1);
        $this->assertEventDispatchedExactly('bronto_email_send_after', 1);
    }
}
