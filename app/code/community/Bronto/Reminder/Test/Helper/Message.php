<?php
class Bronto_Reminder_Test_Helper_Message
    extends EcomDev_PHPUnit_Test_Case
{
    private $_helper;

    protected function setUp()
    {
	$this->_helper = Mage::helper('bronto_reminder/message');
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function mageHelperShouldProvideCorrectClass()
    {
	$this->assertInstanceOf('Bronto_Reminder_Helper_Message', $this->_helper);
    }
}
