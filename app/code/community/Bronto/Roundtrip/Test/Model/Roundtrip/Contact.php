<?php
class Bronto_RoundTrip_Test_Model_Roundtrip_Contact
    extends EcomDev_PHPUnit_Test_Case
{
    const TOKEN = '53873730-F77B-4B0D-9840-43F21846F991';

	//	{{{	getContactShouldReturnContact()

	/**
	 * @test
	 * @group jmk
	 * @group model
	 */
	public function getContactShouldReturnContact()
	{
        $api = Bronto_Common_Model_Api::getInstance(self::TOKEN);
        $builder = new Bronto_Roundtrip_Model_Contact_Builder($api);
        $contact = $builder->getContact();

        $this->assertInstanceOf('Bronto_Api_Contact_Row', $contact);
	}

	//	}}}
}
