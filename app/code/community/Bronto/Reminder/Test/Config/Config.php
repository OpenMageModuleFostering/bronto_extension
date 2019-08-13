<?php
class Bronto_Reminder_Test_Config_Config
        extends EcomDev_PHPUnit_Test_Case_Config
{
    public function blocksProvider()
    {
        return array();
    }

    public function modelsProvider()
    {
        return array(
            array('bronto_reminder/rule', 'Bronto_Reminder_Model_Rule'),
	    array('bronto_reminder/email_message', 'Bronto_Reminder_Model_Email_Message'),
	    array('bronto_reminder/mysql4_rule', 'Bronto_Reminder_Model_Mysql4_rule'),
        );
    }

    public function helpersProvider()
    {
        return array(
            array('bronto_reminder/data', 'Bronto_Reminder_Helper_Data'),
	    array('bronto_reminder/message', 'Bronto_Reminder_Helper_Message'),
	    array('bronto_reminder/contact', 'Bronto_Reminder_Helper_Contact'),
        );
    }

    public function observersProvider()
    {
        return array(
            array('adminhtml', 'controller_action_predispatch', 'bronto_reminder/observer', 'checkBrontoRequirements'),
            array('global', 'salesrule_rule_get_coupon_types', 'bronto_reminder/observer', 'getCouponTypes'),            
            array('global', 'adminhtml_promo_quote_edit_tab_main_prepare_form', 'bronto_reminder/observer', 'updatePromoQuoteTabMainForm'),
            array('frontend', 'sales_quote_save_before', 'bronto_reminder/observer', 'storeGuestEmailCheckout'),
        );
    }

    public function dependsProvider()
    {
	return array(
	    array('Bronto_Common'),
	    array('Mage_Wishlist'),
	    array('Mage_SalesRule'),
	);
    }


    public function definedLayoutFilesProvider()
    {
        return array(
            array('adminhtml', 'bronto/reminder.xml'),
        );
    }

    public function themeLayoutFilesExistProvider()
    {
        return array(
            array('adminhtml', 'bronto/reminder.xml', 'default', 'default'),
        );
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertReminderModuleVersionGreaterThanOrEquals()
    {
        $this->assertModuleVersionGreaterThanOrEquals('1.4.10');
    }

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertReminderModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider dependsProvider
     */
    public function assertReminderModuleDepends(
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
    public function assertReminderModelAliases(
            $classAlias,
            $expectedClassName
    ) {
	$this->assertModelAlias($classAlias, $expectedClassName);
    }

    /**
     * test
     * @group amd
     * @group config
     * @dataProvider blocksProvider
     */
    public function assertReminderBlockAliases(
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
    public function assertReminderHelperAliases(
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
    function assertReminderEventObserversDefined (
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
    public function assertReminderLayoutFileDefined (
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
    public function assertReminderLayoutFileExistsInTheme (
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
