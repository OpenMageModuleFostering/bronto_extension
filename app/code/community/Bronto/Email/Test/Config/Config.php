<?php
class Bronto_Email_Test_Config_Config
    extends EcomDev_PHPUnit_Test_Case_Config
{
    public function blocksProvider()
    {
        return array(
            array('bronto_email/adminhtml_system_config_about', 'Bronto_Email_Block_Adminhtml_System_Config_About'),
            array('bronto_email/adminhtml_system_email_log', 'Bronto_Email_Block_Adminhtml_System_Email_Log'),
	    array('bronto_email/adminhtml_system_email_log_grid', 'Bronto_Email_Block_Adminhtml_System_Email_Log_Grid'),
	    array('bronto_email/adminhtml_system_email_log_grid_renderer_customer', 'Bronto_Email_Block_Adminhtml_System_Email_Log_Grid_Renderer_Customer'),
	    array('bronto_email/adminhtml_system_email_log_grid_renderer_fields', 'Bronto_Email_Block_Adminhtml_System_Email_Log_Grid_Renderer_Fields'),
            array('bronto_email/adminhtml_system_email_template', 'Bronto_Email_Block_Adminhtml_System_Email_Template'),
	    array('bronto_email/adminhtml_system_email_template_edit', 'Bronto_Email_Block_Adminhtml_System_Email_Template_Edit'),
	    array('bronto_email/adminhtml_system_email_template_edit_form', 'Bronto_Email_Block_Adminhtml_System_Email_Template_Edit_Form'),
	    array('bronto_email/adminhtml_system_email_template_grid', 'Bronto_Email_Block_Adminhtml_System_Email_Template_Grid'),
	    array('bronto_email/adminhtml_system_email_template_grid_renderer_message', 'Bronto_Email_Block_Adminhtml_System_Email_Template_Grid_Renderer_Message'),
        );
    }

    public function resourceModelProvider()
    {
        return array(
	    array('bronto_email_mysql4/log', 'Bronto_Email_Model_Mysql4_Log'),
	    array('bronto_email_mysql4/log_collection', 'Bronto_Email_Model_Mysql4_Log_Collection'),
	);
    }

    public function modelsProvider()
    {
        return array(
            array('bronto_email/log', 'Bronto_Email_Model_Log'),
	    array('bronto_email/email_message', 'Bronto_Email_Model_Email_Message'),
	    array('bronto_email/template_import', 'Bronto_Email_Model_Template_Import'),
	    array('bronto_email/observer', 'Bronto_Email_Model_Observer'),
        );
    }

    public function helpersProvider()
    {
        return array(
            array('bronto_email/data', 'Bronto_Email_Helper_Data'),
	    array('bronto_email/email', 'Bronto_Email_Helper_Email'),
	    array('bronto_email/field', 'Bronto_Email_Helper_Field'),
	    array('bronto_email/message', 'Bronto_Email_Helper_Message'),
	    array('bronto_email/contact', 'Bronto_Email_Helper_Contact'),
        );
    }

    public function observersProvider()
    {
        return array(
	    array('adminhtml', 'controller_action_predispatch', 'bronto_email/observer', 'checkBrontoRequirements')
	);
    }

    public function definedLayoutFilesProvider()
    {
        return array(
            array('adminhtml', 'bronto/email.xml'),
        );
    }
    
    public function themeLayoutFilesExistProvider()
    {
        return array(
            array('adminhtml', 'bronto/email.xml', 'default', 'default'),
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
    public function assertEmailModuleVersionGreaterThanOrEquals()
    {
        $this->assertModuleVersionGreaterThanOrEquals('1.1.1');
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertEmailModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider dependsProvider
     */
    public function assertEmailModuleDepends(
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
    public function assertEmailModelAliases(
            $classAlias,
            $expectedClassName
    ) {
        $this->assertModelAlias($classAlias, $expectedClassName);
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider resourceModelProvider
     */
    public function assertEmailResourceModelAliases(
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
    public function assertEmailBlockAliases(
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
    public function assertEmailHelperAliases(
            $classAlias,
            $expectedClassName
    ) {
        $this->assertHelperAlias($classAlias, $expectedClassName);
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider observersProvider
     */
    function assertEmailEventObserverDefined (
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
     * @test
     * @group amd
     * @group config
     * @dataProvider definedLayoutFilesProvider
     */
    public function assertEmailLayoutFileDefined (
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
    public function assertEmailLayoutFileExistsInTheme (
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
