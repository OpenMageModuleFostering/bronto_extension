<?php
class Bronto_ConflictChecker_Test_Model_Path_Locator_Stack
    extends EcomDev_PHPUnit_Test_Case
{
	//	{{{	getPathWithStackShouldReturnPathAsString()

	/**
	 * @test
	 * @group jmk
	 * @group model
	 */
	public function getPathWithStackShouldReturnPathAsString()
	{
        $locator = new Bronto_ConflictChecker_Model_Path_Locator_Stack(new SplStack);

        $xmlString = "<config><path><to><node>node value</node></to></path></config>";
        $xml = new Bronto_ConflictChecker_Model_Lib_Varien_Simplexml_Element($xmlString);
        list($element) = $xml->xpath('/config/path/to/node');

        $path = $locator->getPath($element);
        $this->assertEquals('config/path/to/node/', $locator->getpath($element));
	}

	//	}}}
}
