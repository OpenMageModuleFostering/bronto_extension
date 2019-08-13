<?php
class Bronto_PermissionChecker_Test_Config_Config
    extends EcomDev_PHPUnit_Test_Case_Config
{
    public function blocksProvider()
    {
        return array(
            array('bronto_permissionchecker/printer', 'Bronto_PermissionChecker_Block_Printer'),
            array('bronto_permissionchecker/adminhtml_system_config_about', 'Bronto_PermissionChecker_Block_Adminhtml_System_Config_About'),
            array('bronto_permissionchecker/adminhtml_system_config_permissionchecker', 'Bronto_PermissionChecker_Block_Adminhtml_System_Config_Permissionchecker'),
        );
    }

    public function resourceModelProvider()
    {
        return array();
    }

    public function modelsProvider()
    {
        return array(
            array('bronto_permissionchecker/validator_checker', 'Bronto_PermissionChecker_Model_Validator_Checker'),
	    array('bronto_permissionchecker/validator_directory', 'Bronto_PermissionChecker_Model_Validator_Directory'),
	    array('bronto_permissionchecker/validator_file', 'Bronto_PermissionChecker_Model_Validator_File'),
	    array('bronto_permissionchecker/validator_group', 'Bronto_PermissionChecker_Model_Validator_Group'),
	    array('bronto_permissionchecker/validator_owner', 'Bronto_PermissionChecker_Model_Validator_Owner'),
	    array('bronto_permissionchecker/validator_printer', 'Bronto_PermissionChecker_Model_Validator_Printer'),
	    array('bronto_permissionchecker/validator_validatorabstract', 'Bronto_PermissionChecker_Model_Validator_Validatorabstract'),
	    array('bronto_permissionchecker/validator_validatorinterface', 'Bronto_PermissionChecker_Model_Validator_Validatorinterface'),
	    array('bronto_permissionchecker/validator_filter_patterniterator', 'Bronto_PermissionChecker_Model_Validator_Filter_Patterniterator'),
        );
    }

    public function helpersProvider()
    {
        return array(
            array('bronto_permissionchecker/data', 'Bronto_PermissionChecker_Helper_Data'),
        );
    }

    public function observersProvider()
    {
        return array();
    }

    public function definedLayoutFilesProvider()
    {
        return array();
    }

    public function themeLayoutFilesExistProvider()
    {
        return array();
    }

    public function dependsProvider()
    {
        return array();
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertPermissionCheckerModuleVersionGreaterThanOrEquals()
    {
        $this->assertModuleVersionGreaterThanOrEquals('0.1.0');
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertPermissionCheckerModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * test
     * @group amd
     * @group config
     * @dataProvider dependsProvider
     */
    public function assertPermissionCheckerModuleDepends(
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
    public function assertPermissionCheckerModelAliases(
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
    public function assertPermissionCheckerResourceModelAliases(
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
    public function assertPermissionCheckerBlockAliases(
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
    public function assertPermissionCheckerHelperAliases(
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
    function assertPermissionCheckerEventObserverDefined (
        $area,
        $eventName,
        $observerClassAlias,
        $observerMethod
    ) {
        $this->assertEventObserverDefined(
            $area,
            $eventName,
            $observerClassAlias,
            $observerMethod
        );
    }

    /**
     * test
     * @group amd
     * @group config
     * @dataProvider definedLayoutFilesProvider
     */
    public function assertPermissionCheckerLayoutFileDefined (
        $area,
        $expectedFileName
    ) {
        $this->assertLayoutFileDefined($area, $expectedFileName);
    }

    /**
     * test
     * @group amd
     * @group config
     * @dataProvider themeLayoutFilesExistProvider
     */
    public function assertPermissionCheckerLayoutFileExistsInTheme (
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
