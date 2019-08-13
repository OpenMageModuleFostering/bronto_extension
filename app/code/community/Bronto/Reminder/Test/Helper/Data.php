<?php
class Bronto_Reminder_Test_Helper_Data
    extends EcomDev_PHPUnit_Test_Case
{
    private $_helper;

    protected function setUp()
    {
	$this->_helper = Mage::helper('bronto_reminder/data');
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function mageHelperShouldProvideCorrectClass()
    {
	$this->assertInstanceOf('Bronto_Reminder_Helper_Data', $this->_helper);
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function isEnabled_ShouldReturnBool()
    {
        $isEnabled = $this->_helper->isEnabled();
        $this->assertInternalType('bool', $isEnabled);
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function isEnabled_ShouldReturnTrue()
    {
	Mage::getConfig()->saveConfig('bronto_reminder/settings/enabled', 1);
	Mage::getConfig()->reinit();
	Mage::app()->reinitStores();
        $this->assertTrue($this->_helper->isEnabled());
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function disableModule_ShouldDisableModule()
    {
        $this->_helper->disableModule();
        Mage::getConfig()->reinit();
	Mage::app()->reinitStores();
	$this->assertFalse($this->_helper->isEnabled());
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function getCronInterval_ShouldReturnInt()
    {
	$this->assertInternalType('int', $this->_helper->getCronInterval());
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function getOneRunLimit_ShouldReturnInt()
    {
	$this->assertInternalType('int', $this->_helper->getOneRunLimit());
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function getEmailIdentity_ShouldReturnString()
    {
	$this->assertInternalType('string', $this->_helper->getEmailIdentity());
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function getSendFailureThreshold_ShouldReturnInt()
    {
	$this->assertInternalType('int', $this->_helper->getSendFailureThreshold());
    }
}
