<?php

class Bronto_RoundTrip_Test_Model_Roundtrip
    extends EcomDev_PHPUnit_Test_Case
{
	//	{{{	testProcessOrderShouldFailGracefully()

	/**
     * Test that if there is an exception, the bronto order is successfully
     * cleaned up so the next test will be able to run.
     *
	 * @test
	 * @group jmk
	 * @group model
	 */
	public function processOrderShouldFailGracefully()
	{
        $sessions = array('admin/session', 'adminhtml/session', 'core/session');
        foreach ($sessions as $session) {
            $sessionMock = $this->getModelMockBuilder($session)
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();
            $this->replaceByMock('singleton', $session, $sessionMock);
        }

        //  Create helper mock that will be used in logic
        $helper = $this->getHelperMock('bronto_common/data', array('validApiTokens'));
        $helper->expects($this->once())
            ->method('validApiTokens')
            ->will($this->returnValue(true));

        //  Inject helper object as item to be invoked when static
        //  method called to get helper
        $this->replaceByMock('helper', 'bronto_common/data', $helper);

        //  Mock the roundtrip object so we can
        //  control how functionality is controlled
        $roundTrip = $this->getModelMock(
            'bronto_roundtrip/roundtrip',
            array(
                '_testSandboxConnect',
                '_testCreateContact',
                '_testAddContactToList',
            )
        );

        //  This is the final object we need to verify
        $orderRowMock = $this->getMock('Bronto_Api_Order_Row', array('persist', 'read', 'delete'));
        $orderRowMock->expects($this->any())
            ->method('read');
        //  Throw a random exception on this method to ensure we kick down
        //  into the outer catch stmt.
        $orderRowMock->expects($this->any())
            ->method('persist')
            ->will($this->throwException(new RunTimeException('Unexpected Error.')));
        //  don't try to actually delete anything here, just ensure we hit it.
        $orderRowMock->expects($this->any())
            ->method('delete');

        //  Mock the order object used to the get order row
        $orderObjectMock = $this->getMock('Bronto_Api_Order', array('createRow'));
        $orderObjectMock->expects($this->any())
            ->method('createRow')
            ->will($this->returnValue($orderRowMock));

        //  Mock the API object passed into the _testProcessOrder method
        $apiMock = $this->getMock('Bronto_Api', array('getOrderObject'));
        $apiMock->expects($this->any())
            ->method('getOrderObject')
            ->will($this->returnValue($orderObjectMock));

        //  Don't care about any of the following methods - just need to be
        //  Mocked so they don't choke the system when testing.
        $roundTrip->expects($this->any())
            ->method('_testSandboxConnect')
            ->will($this->returnValue($apiMock));

        $roundTrip->expects($this->any())
            ->method('_testCreateContact')
            ->will($this->returnValue(true));

        $roundTrip->expects($this->any())
            ->method('_testAddContactToList')
            ->will($this->returnValue(true));

        $roundTrip->processRoundtrip();
	}

	//	}}}
}
