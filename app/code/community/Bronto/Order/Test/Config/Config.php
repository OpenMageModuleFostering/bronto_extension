<?php
class Bronto_Order_Test_Config_Config
    extends EcomDev_PHPUnit_Test_Case_Config
{
    //  {{{ blocksProvider()

    public function blocksProvider()
    {
        return array(
            array('bronto_order/bta', 'Bronto_Order_Block_Bta'),
	    array('bronto_order/adminhtml_sales_order_view_tab_info', 'Bronto_Order_Block_Adminhtml_Sales_Order_View_Tab_Info'),
	    array('bronto_order/adminhtml_system_config_about', 'Bronto_Order_Block_Adminhtml_System_Config_About'),
	    array('bronto_order/adminhtml_system_config_cron', 'Bronto_Order_Block_Adminhtml_System_Config_Cron'),
	    array('bronto_order/adminhtml_widget_button_reset', 'Bronto_Order_Block_Adminhtml_Widget_Button_Reset'),
	    array('bronto_order/adminhtml_widget_button_run', 'Bronto_Order_Block_Adminhtml_Widget_Button_Run'),
        );
    }

    //  }}}
    //  {{{ resourceModelProvider()

    public function resourceModelProvider()
    {
        return array(
	    array('bronto_order_resource/setup', 'Bronto_Order_Model_Resource_Setup'),
	    array('bronto_order_resource/order_collection', 'Bronto_Order_Model_Resource_Order_Collection'),
	);
    }

    //  }}}
    //  {{{ modelsProvider()

    public function modelsProvider()
    {
        return array(
            array('bronto_order/system_config_backend_cron', 'Bronto_Order_Model_System_Config_Backend_Cron'),
	    array('bronto_order/system_config_source_description', 'Bronto_Order_Model_System_Config_Source_Description'),
	    array('bronto_order/system_config_source_limit', 'Bronto_Order_Model_System_Config_Source_Limit'),
        );
    }

    //  }}}
    //  {{{ helpersProvider()

    public function helpersProvider()
    {
        return array(
            array('bronto_order/data', 'Bronto_Order_Helper_Data'),
        );
    }

    //  }}}
    //  {{{ observersProvider()

    public function observersProvider()
    {
        return array(
            array('global', 'sales_order_save_before', 'bronto_order/order_observer', 'markOrderForReimport'),
	    array('frontend', 'sales_quote_save_before', 'bronto_order/quote_observer', 'addTidToQuote'),
	    array('adminhtml', 'controller_action_predispatch', 'bronto_order/observer', 'checkBrontoRequirements'),
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

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertOrderModuleVersionGreaterThanOrEquals()
    {
        $this->assertModuleVersionGreaterThanOrEquals('1.1.5');
    }

    //  {{{ assertOrderModuleInLocalCodePool()

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertOrderModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    //  }}}
    //  {{{ assertOrderModelAliases()

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider modelsProvider
     */
    public function assertOrderModelAliases(
        $classAlias,
	$expectedClassName
    ) {
	$this->assertModelAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertOrderResourceModelAliases()

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider resourceModelProvider
     */
    public function assertOrderResourceModelAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertResourceModelAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertOrderBlockAliases()

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider blocksProvider
     */
    public function assertOrderBlockAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertBlockAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertOrderHelperAliases()

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider helpersProvider
     */
    public function assertOrderHelperAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertHelperAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertOrderEventObserversDefined()

    /**
     * @test
     * @group config
     * @group amd
     * @dataProvider observersProvider
     */
    public function assertOrderEventObserversDefined(
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
    //  {{{ assertOrderLayoutFileDefined()

    /**
     * test
     * @group config
     * @group amd
     * @dataProvider definedLayoutFilesProvider
     */
    public function assertOrderLayoutFileDefined($area, $expectedFileName)
    {
        $this->assertLayoutFileDefined($area, $expectedFileName);
    }

    //  }}}
    //  {{{ assertOrderLayoutFileExistsForDefaultTheme()

    /**
     * test
     * @group config
     * @group amd
     * @dataProvider themeLayoutFilesExistProvider
     */
    public function assertOrderLayoutFileExistsForDefaultTheme(
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
