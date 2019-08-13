<?php

class Bronto_ConflictChecker_Test_Model_Path_Locator_Factory
    extends EcomDev_PHPUnit_Test_Case
{
	//	{{{	getLocatorShouldReturnCorrectModel()

	/**
	 * @test
	 * @group jmk
	 * @group model
	 */
	public function getLocatorShouldReturnCorrectModel()
	{
        $factory = new Bronto_ConflictChecker_Model_Path_Locator_Factory;

        $locator = $factory->getLocator();
	}

	//	}}}
}
