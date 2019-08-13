<?php
class Bronto_Reminder_Test_Helper_Contact
    extends EcomDev_PHPUnit_Test_Case
{
    private $_helper;

    protected function setUp()
    {
	$this->_helper = Mage::helper('bronto_reminder/contact');
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function helperShouldProvideCorrectClass()
    {
	$this->assertInstanceOf('Bronto_Reminder_Helper_Contact', $this->_helper);
    }

    /**
     * @test
     * @group amd
     * @group helper
     */
    public function getContactByEmail_ShouldReturnBrontoApiContactRow()
    {
        $this->assertInstanceOf('Bronto_Api_Contact_Row', $this->_helper->getContactByEmail('email@domain.com'));
    }
}
