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
    public function checkBrontoRequirementsShouldReturnNull()
    {
        $sessionMock = $this->getModelMockBuilder('admin/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'admin/session', $sessionMock);
        $this->assertNull($this->_model->checkBrontoRequirements(new Varien_Event_Observer()));
    }

    /**
     * test
     * @group amd
     * @group model
     */
    public function getCouponTypesShouldReturnSelf()
    {
        $this->assertInstanceOf(
            'Bronto_Reminder_Model_Observer',
            $this->_model->getCouponTypes()
        );
    }

    public function getCouponTypesShouldSetTransport()
    {
        $this->markTestSkipped();
    }
}
