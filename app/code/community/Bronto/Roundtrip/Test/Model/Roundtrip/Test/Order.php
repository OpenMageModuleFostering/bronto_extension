<?php

class Bronto_RoundTrip_Test_Model_Roundtrip_Test_Order
    extends EcomDev_PHPUnit_Test_Case
{
//    const TOKEN = '53873730-F77B-4B0D-9840-43F21846F991';
    const TOKEN = '077E2083-BBED-4B16-A3AB-E1095EAA2E58';

	//	{{{	processOrdersShouldPlaceOrderWithApi()

	/**
     * Test that if there is an exception, the bronto order is successfully
     * cleaned up so the next test will be able to run.
     *
	 * @test
	 * @group jmk
	 * @group model
	 */
	public function processOrdersShouldPlaceOrderWithApi()
	{
        //  Mock the order object used to the get order row
        $api = Bronto_Common_Model_Api::getInstance(self::TOKEN);
        $builder = new Bronto_Roundtrip_Model_Contact_Builder($api);

        $order   = $api->getOrderObject();
        $contact = $builder->getContact();

        $testOrder = Mage::getModel('bronto_roundtrip/roundtrip_test_order');
        $testOrder->processOrder($order, $contact);
	}

	//	}}}
}
