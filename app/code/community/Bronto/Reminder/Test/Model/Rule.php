<?php
class Bronto_Reminder_Test_Model_Rule
    extends EcomDev_PHPUnit_Test_Case
{
	private $_fixture;

	public function setUp()
	{
		$this->_fixture = Mage::getModel('bronto_reminder/rule');
	}

	/**
	 * @test
	 * @group amd
	 * @group model
	 */
	public function getConditionsInstance_shouldReturnObject()
	{
		$instance = $this->_fixture->getConditionsInstance();

		$this->assertInstanceOf('Bronto_Reminder_Model_Rule_Condition_Combine_Root', $instance);
	}

	/**
	 * @test
	 * @group amd
	 * @group model
	 */
	public function getWebsiteIds_shouldReturnArrayOfIds()
	{
		$websiteIds = $this->_fixture->getWebsiteIds();
		
		$this->assertInternalType('array', $websiteIds);
	}

	/**
	 * @test
	 * @group amd
	 * @group model
	 */
	public function sendReminderEmails_withDontSendReturnsObject()
	{
		$rule = $this->_fixture->sendReminderEmails(true);

		$this->assertInstanceOf('Bronto_Reminder_Model_Rule', $rule);
	}
}
