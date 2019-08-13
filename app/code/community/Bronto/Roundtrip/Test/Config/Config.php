<?php
class Bronto_Roundtrip_Test_Config_Config
	extends EcomDev_PHPUnit_Test_Case_Config
{
    public function blocksProvider()
    {
        return array(
            array('bronto_roundtrip/adminhtml_system_config_about', 'Bronto_Roundtrip_Block_Adminhtml_System_Config_About'),
            array('bronto_roundtrip/adminhtml_system_config_status', 'Bronto_Roundtrip_Block_Adminhtml_System_Config_Status'),
            array('bronto_roundtrip/adminhtml_widget_button_run', 'Bronto_Roundtrip_Block_Adminhtml_Widget_Button_Run'),
        );
    }

    public function resourceModelProvider()
    {
        return array();
    }

    public function modelsProvider()
    {
        return array(
            array('bronto_roundtrip/roundtrip', 'Bronto_Roundtrip_Model_Roundtrip'),
        );
    }

    public function helpersProvider()
    {
        return array(
            array('bronto_roundtrip/data', 'Bronto_Roundtrip_Helper_Data'),
        );
    }

    public function observersProvider()
    {
	return array();
    }

    public function definedLayoutFilesProvider()
    {
	return array(
	    array('adminhtml', 'bronto/roundtrip.xml'),
	);
    }

    public function themeLayoutFilesExistProvider()
    {
	return array(
	    array('adminhtml', 'bronto/roundtrip.xml', 'default', 'default'),
	);
    }

    public function dependsProvider()
    {
	return array(
	    array('Bronto_Common'),
	);
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertRoundtripModuleVersionGreaterThanOrEquals()
    {
        $this->assertModuleVersionGreaterThanOrEquals('0.1.0');
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertRoundtripModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider dependsProvider
     */
    public function assertRoundtripModuleDepends(
	$requiredModuleName
    ) {
	$this->assertModuleDepends($requiredModuleName);
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider modelsProvider
     */
    public function assertRoundtripModelAliases(
            $classAlias,
            $expectedClassName
    ) {
	$this->assertModelAlias($classAlias, $expectedClassName);
    }

    /**
     * test
     * @group amd
     * @group config
     * @dataProvider resourceModelProvider
     */
    public function assertRoundtripResourceModelAliases(
	$classAlias,
	$expectedClassName
    ) {
	$this->assertResourceModelAlias($classAlias, $expectedClassName);
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider blocksProvider
     */
    public function assertRoundtripBlockAliases(
            $classAlias,
            $expectedClassName
    ) {
        $this->assertBlockAlias($classAlias, $expectedClassName);
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider helpersProvider
     */
    public function assertRoundtripHelperAliases(
            $classAlias,
            $expectedClassName
    ) {
        $this->assertHelperAlias($classAlias, $expectedClassName);
    }

    /**
     * test
     * @group amd
     * @group config
     * @dataProvider observersProvider
     */
    public function assertRoundtripEventObserversDefined(
	$area,
	$eventName,
	$observerClassAlias,
	$observerMethod
    ) {
	$this->assertEventObserversDefined(
	    $area,
	    $eventName,
	    $observerClassAlias,
	    $observerMethod
	);
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider definedLayoutFilesProvider
     */
    public function assertRoundtripLayoutFileDefined(
	$area, 
	$expectedFileName
    ) {
	$this->assertLayoutFileDefined($area, $expectedFileName);
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider themeLayoutFilesExistProvider
     */
    public function assertRoundtripLayoutFileExistsInTheme (
	$area,
	$filename,
	$theme,
	$designPackage
    ) {
	$this->assertLayoutFileExistsInTheme(
	    $area,
	    $filename,
	    $theme,
	    $designPackage
	);
    }
}

