<?php
class Bronto_ConflictChecker_Test_Model_Path_Locator_Array
    extends EcomDev_PHPUnit_Test_Case
{
	//	{{{	getPathWithArrayShouldReturnPathAsString()

	/**
	 * @test
	 * @group jmk
	 * @group model
	 */
	public function getPathWithArrayShouldReturnPathAsString()
	{
        $locator = new Bronto_ConflictChecker_Model_Path_Locator_Array(array());

        $xmlString = "<config><path><to><node>node value</node></to></path></config>";
        $xml = new Bronto_ConflictChecker_Model_Lib_Varien_Simplexml_Element($xmlString);
        list($element) = $xml->xpath('/config/path/to/node');

        $path = $locator->getPath($element);
        $this->assertEquals('config/path/to/node/', $locator->getpath($element));
	}

	//	}}}
}
