<?php
class Bronto_Common_Test_Config_Config
    extends EcomDev_PHPUnit_Test_Case_Config
{
    public function blocksProvider()
    {
        return array(
            array('bronto_common/adminhtml_system_config_about', 'Bronto_Common_Block_Adminhtml_System_Config_About'),
            array('bronto_common/adminhtml_system_config_cron', 'Bronto_Common_Block_Adminhtml_System_Config_Cron'),
            array('bronto_common/adminhtml_system_config_form_field', 'Bronto_Common_Block_Adminhtml_System_Config_Form_Field'),
            array('bronto_common/adminhtml_system_config_form_field_apitoken', 'Bronto_Common_Block_Adminhtml_System_Config_Form_Field_Apitoken'),
            array('bronto_common/adminhtml_system_config_form_field_hidden', 'Bronto_Common_Block_Adminhtml_System_Config_Form_Field_Hidden'),
            array('bronto_common/adminhtml_system_config_form_field_list', 'Bronto_Common_Block_Adminhtml_System_Config_Form_Field_List'),
        );
    }

    public function modelsProvider()
    {
        return array(
            array('bronto_common/api', 'Bronto_Common_Model_Api'),
            array('bronto_common/email_message', 'Bronto_Common_Model_Email_Message'),
            array('bronto_common/email_message_filter', 'Bronto_Common_Model_Email_Message_Filter'),
	    array('bronto_common/system_config_backend_cron', 'Bronto_Common_Model_System_Config_Backend_Cron'),
	    array('bronto_common/system_config_backend_token', 'Bronto_Common_Model_System_Config_Backend_Token'),
	    array('bronto_common/system_config_source_fields', 'Bronto_Common_Model_System_Config_Source_Fields'),
	    array('bronto_common/system_config_source_list', 'Bronto_Common_Model_System_Config_Source_List'),
	    array('bronto_common/system_config_source_contact_status', 'Bronto_Common_Model_System_Config_Source_Contact_Status'),
	    array('bronto_common/system_config_source_cron_frequency', 'Bronto_Common_Model_System_Config_Source_Cron_Frequency'),
	    array('bronto_common/system_config_source_cron_minutes', 'Bronto_Common_Model_System_Config_Source_Cron_Minutes',),
        );
    }

    public function resourceModelProvider()
    {
	return array(
	    array('bronto_common/resource_setup', 'Bronto_Common_Model_Resource_Setup'),
	);
    }

    public function observersProvider()
    {
	return array(
	    array('adminhtml', 'controller_action_predispatch', 'bronto_common/observer', 'checkBrontoRequirements')
	);
    }

    public function helpersProvider()
    {
        return array(
            array('bronto_common/data', 'Bronto_Common_Helper_Data'),
            array('bronto_common/message', 'Bronto_Common_Helper_Message'),
            array('bronto_common/contact', 'Bronto_Common_Helper_Contact'),
            array('bronto_common/field', 'Bronto_Common_Helper_Field'),
            array('bronto_common/product', 'Bronto_Common_Helper_Product'),
        );
    }

    public function dependsProvider()
    {
	return array(
	    array('Mage_Adminhtml'),
            array('Mage_Customer'),
            array('Mage_Checkout'),
            array('Mage_Sales'),
	);
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertCommonModuleVersionGreaterThanOrEquals()
    {
	$this->assertModuleVersionGreaterThanOrEquals('1.7.0');
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertCommonModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider dependsProvider
     */
    public function assertCommonModuleDepends(
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
    public function assertCommonModelAliases(
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
    public function assertCommonResourceModelAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertResourceModelAlias($classAlias, $expectedClassName);
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider observersProvider
     */
    public function assertCommonEventObserversDefined(
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
     * @dataProvider blocksProvider
     */
    public function assertCommonBlockAliases(
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
    public function assertCommonHelperAliases(
            $classAlias,
            $expectedClassName
    ) {
        $this->assertHelperAlias($classAlias, $expectedClassName);
    }
}
