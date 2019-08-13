<?php
class Bronto_ConflictChecker_Test_Config_Config
    extends EcomDev_PHPUnit_Test_Case_Config
{
    //  {{{ blocksProvider()

    public function blocksProvider()
    {
        return array(
            array('bronto_conflictchecker/adminhtml_system_config_conflictchecker', 'Bronto_ConflictChecker_Block_Adminhtml_System_Config_Conflictchecker'),
        );
    }

    //  }}}
    //  {{{ resourceModelProvider()

    public function resourceModelProvider()
    {
        return array(
	    array('bronto_conflictchecker/mysql4_core_config', 'Bronto_ConflictChecker_Model_Mysql4_Core_Config'),
	    array('bronto_conflictchecker/resource_core_config', 'Bronto_ConflictChecker_Model_Resource_Core_Config'),
	);
    }

    //  }}}
    //  {{{ modelsProvider()

    public function modelsProvider()
    {
        return array(
            array('bronto_conflictchecker/core_config_element', 'Bronto_ConflictChecker_Model_Core_Config_Element'),
            array('bronto_conflictchecker/core_config_base', 'Bronto_ConflictChecker_Model_Core_Config_Base'),
            array('bronto_conflictchecker/core_config', 'Bronto_ConflictChecker_Model_Core_Config'),
            array('bronto_conflictchecker/config_blocks', 'Bronto_ConflictChecker_Model_Config_Blocks'),
            array('bronto_conflictchecker/config_printer', 'Bronto_ConflictChecker_Model_Config_Printer'),
            array('bronto_conflictchecker/config_checker', 'Bronto_ConflictChecker_Model_Config_Checker'),
            array('bronto_conflictchecker/config_configabstract', 'Bronto_ConflictChecker_Model_Config_Configabstract'),
            array('bronto_conflictchecker/config_configinterface', 'Bronto_ConflictChecker_Model_Config_Configinterface'),
            array('bronto_conflictchecker/config_datastore', 'Bronto_ConflictChecker_Model_Config_Datastore'),
            array('bronto_conflictchecker/config_helpers', 'Bronto_ConflictChecker_Model_Config_Helpers'),
            array('bronto_conflictchecker/config_models', 'Bronto_ConflictChecker_Model_Config_Models'),
            array('bronto_conflictchecker/config_printer', 'Bronto_ConflictChecker_Model_Config_Printer'),
            array('bronto_conflictchecker/config_resources', 'Bronto_ConflictChecker_Model_Config_Resources'),
        );
    }

    //  }}}
    //  {{{ helpersProvider()

    public function helpersProvider()
    {
        return array(
            array('bronto_conflictchecker/data', 'Bronto_ConflictChecker_Helper_Data'),
        );
    }

    //  }}}
    //  {{{ observersProvider()

    public function observersProvider()
    {
        return array(
            /*array('global', 'customer_save_before', 'user/observer', 'accountUpgrade'),*/
        );
    }

    //  }}}
    //  {{{ definedLayoutFilesProvider()

    public function definedLayoutFilesProvider()
    {
        return array(
            /*array('frontend', 'bronto/user.xml'),*/
        );
    }

    //  }}}
    //  {{{ themeLayoutFilesExistProvider()

    public function themeLayoutFilesExistProvider()
    {
        return array(
            /*array('frontend', 'bronto/user.xml', 'adsinc', 'adsinc'),*/
        );
    }

    //  }}}
    
    public function dependsProvider()
    {
	return array(
	    array('Bronto_Common'),
	);
    }

    //  {{{ assertConflictCheckerModuleInLocalCodePool()

    
    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertCommonModuleVersionGreaterThanOrEquals()
    {
        $this->assertModuleVersionGreaterThanOrEquals('0.1.0');
    }


    /**
     * @test
     * @group jmk
     * @group config
     */
    public function assertConflictCheckerModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    //  }}}

    /**
     * Note: Switched to NotDepends to ensure this module doesn't become dependent upon common
     * @test
     * @group amd
     * @group config
     * @dataProvider dependsProvider
     */
    public function assertConflictCheckerNotDepends(
	$requiredModuleName
    ) {
	$this->assertModuleNotDepends($requiredModuleName);
    }

    //  {{{ assertConflictCheckerModelAliases()

    /**
     * @test
     * @group jmk
     * @group config
     * @dataProvider modelsProvider
     */
    public function assertConflictCheckerModelAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertModelAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertConflictCheckerResourceModelAliases()

    /**
     * test
     * @group jmk
     * @group config
     * @dataProvider resourceModelProvider
     */
    public function assertConflictCheckerResourceModelAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertResourceModelAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertConflictCheckerBlockAliases()

    /**
     * @test
     * @group jmk
     * @group config
     * @dataProvider blocksProvider
     */
    public function assertConflictCheckerBlockAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertBlockAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertConflictCheckerHelperAliases()

    /**
     * @test
     * @group jmk
     * @group config
     * @dataProvider helpersProvider
     */
    public function assertConflictCheckerHelperAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertHelperAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertConflictCheckerEventObserversDefined()

    /**
     * test
     * @group config
     * @group jmk
     * @dataProvider observersProvider
     */
    public function assertConflictCheckerEventObserversDefined(
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

    //  }}}
    //  {{{ assertConflictCheckerLayoutFileDefined()

    /**
     * test
     * @group config
     * @group jmk
     * @dataProvider definedLayoutFilesProvider
     */
    public function assertConflictCheckerLayoutFileDefined($area, $expectedFileName)
    {
        $this->assertLayoutFileDefined($area, $expectedFileName);
    }

    //  }}}
    //  {{{ assertConflictCheckerLayoutFileExistsForDefaultTheme()

    /**
     * test
     * @group config
     * @group jmk
     * @dataProvider themeLayoutFilesExistProvider
     */
    public function assertConflictCheckerLayoutFileExistsForDefaultTheme(
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

    //  }}}
}
