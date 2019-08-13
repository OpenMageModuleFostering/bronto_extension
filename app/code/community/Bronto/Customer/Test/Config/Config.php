<?php
class Bronto_Customer_Test_Config_Config
    extends EcomDev_PHPUnit_Test_Case_Config
{
    public function blocksProvider()
    {
        return array(
            array('bronto_customer/adminhtml_system_config_about', 'Bronto_Customer_Block_Adminhtml_System_Config_About'),
            array('bronto_customer/adminhtml_system_config_form_fieldset_attributes', 'Bronto_Customer_Block_Adminhtml_System_Config_Form_Fieldset_Attributes'),
            array('bronto_customer/adminhtml_system_config_form_fieldset_attributes_address', 'Bronto_Customer_Block_Adminhtml_System_Config_Form_Fieldset_Attributes_Address'),
            array('bronto_customer/adminhtml_system_config_form_fieldset_attributes_customer', 'Bronto_Customer_Block_Adminhtml_System_Config_Form_Fieldset_Attributes_Customer'),
        );
    }

    public function modelsProvider()
    {
        return array();
    }

    public function resourceModelProvider()
    {
	return array();
    }

    public function observersProvider()
    {
	return array();
    }

    public function helpersProvider()
    {
        return array(
            array('bronto_customer/data', 'Bronto_Customer_Helper_Data'),
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
    public function assertCustomerModuleVersionGreaterThanOrEquals()
    {
        $this->assertModuleVersionGreaterThanOrEquals('1.0.0');
    }


    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertCustomerModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider dependsProvider
     */
    public function assertCustomerModuleDepends(
	$requiredModuleName
    ) {
	$this->assertModuleDepends($requiredModuleName);
    }

    /**
     * test
     * @group amd
     * @group config
     * @dataProvider modelsProvider
     */
    public function assertCustomerModelAliases(
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
    public function assertCustomerResourceModelAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertResourceModelAlias($classAlias, $expectedClassName);
    }

    /**
     * test
     * @group config
     * @group amd
     * @dataProvider observersProvider
     */
    public function assertCustomerEventObserversDefined(
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
    public function assertCustomerBlockAliases(
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
    public function assertCustomerHelperAliases(
            $classAlias,
            $expectedClassName
    ) {
        $this->assertHelperAlias($classAlias, $expectedClassName);
    }
}
