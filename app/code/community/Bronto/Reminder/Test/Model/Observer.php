<?php
class Bronto_Reminder_Test_Model_Observer
    extends EcomDev_PHPUnit_Test_Case
{
    private $_model;

    protected function setUp()
    {
	$this->_model = Mage::getModel('bronto_reminder/observer');
    }

    /**
     * @test
     * @group amd
     * @group model
     */
    public function checkBrontoRequirements_ShouldReturnNull()
    {
	$this->assertNull($this->_model->checkBrontoRequirements(new Varien_Event_Observer()));
    }

    /**
     * test
     * @group amd
     * @group model
     */
    public function getCouponTypes_ShouldReturnSelf()
    {
	$this->assertInstanceOf('Bronto_Reminder_Model_Observer', $this->_model->getCouponTypes());
    }

    public function getCouponTypes_ShouldSetTransport()
    {
	$this->markTestSkipped();
    }
}
