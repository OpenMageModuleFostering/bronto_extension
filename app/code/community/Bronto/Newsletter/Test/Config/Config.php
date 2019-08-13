<?php
class Bronto_Newsletter_Test_Config_Config
    extends EcomDev_PHPUnit_Test_Case_Config
{
    //  {{{ blocksProvider()

    public function blocksProvider()
    {
        return array(
            array('bronto_newsletter/adminhtml_system_config_about', 'Bronto_Newsletter_Block_Adminhtml_System_Config_About'),
	    array('bronto_newsletter/checkout_onepage_newsletter', 'Bronto_Newsletter_Block_Checkout_Onepage_Newsletter'),
        );
    }

    //  }}}
    //  {{{ resourceModelProvider()

    public function resourceModelProvider()
    {
        return array(
	    array('bronto_newsletter_mysql4/queue', 'Bronto_Newsletter_Model_Mysql4_Queue'),
	    array('bronto_newsletter_mysql4/queue_collection', 'Bronto_Newsletter_Model_Mysql4_Queue_Collection'),
	);
    }

    //  }}}
    //  {{{ modelsProvider()

    public function modelsProvider()
    {
        return array(
            array('bronto_newsletter/queue', 'Bronto_Newsletter_Model_Queue'),
        );
    }

    //  }}}
    //  {{{ helpersProvider()

    public function helpersProvider()
    {
        return array(
            array('bronto_newsletter/data', 'Bronto_Newsletter_Helper_Data'),
	    array('bronto_newsletter/contact', 'Bronto_Newsletter_Helper_Contact'),
        );
    }

    //  }}}
    //  {{{ observersProvider()

    public function observersProvider()
    {
        return array(
            array('adminhtml', 'controller_action_predispatch', 'bronto_newsletter/observer', 'checkBrontoRequirements'),
        );
    }

    //  }}}
    //  {{{ definedLayoutFilesProvider()

    public function definedLayoutFilesProvider()
    {
        return array(
            array('frontend', 'bronto/newsletter.xml'),
        );
    }

    //  }}}
    //  {{{ themeLayoutFilesExistProvider()

    public function themeLayoutFilesExistProvider()
    {
        return array(
            array('frontend', 'bronto/newsletter.xml', 'default', 'default'),
        );
    }

    //  }}}

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertNewsletterModuleVersionGreaterThanOrEquals()
    {
        $this->assertModuleVersionGreaterThanOrEquals('1.4.0');
    }

    //  {{{ assertNewsletterModuleInLocalCodePool()

    /**
     * @test
     * @group amd
     * @group config
     */
    public function assertNewsletterModuleInCommunityCodePool()
    {
        $this->assertModuleCodePool('community');
    }

    //  }}}
    //  {{{ assertNewsletterModelAliases()

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider modelsProvider
     */
    public function assertNewsletterModelAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertModelAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertNewsletterResourceModelAliases()

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider resourceModelProvider
     */
    public function assertNewsletterResourceModelAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertResourceModelAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertNewsletterBlockAliases()

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider blocksProvider
     */
    public function assertNewsletterBlockAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertBlockAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertNewsletterHelperAliases()

    /**
     * @test
     * @group amd
     * @group config
     * @dataProvider helpersProvider
     */
    public function assertNewsletterHelperAliases(
        $classAlias,
        $expectedClassName
    ) {
        $this->assertHelperAlias($classAlias, $expectedClassName);
    }

    //  }}}
    //  {{{ assertNewsletterEventObserversDefined()

    /**
     * test
     * @group config
     * @group amd
     * @dataProvider observersProvider
     */
    public function assertNewsletterEventObserversDefined(
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
    //  {{{ assertNewsletterLayoutFileDefined()

    /**
     * test
     * @group config
     * @group amd
     * @dataProvider definedLayoutFilesProvider
     */
    public function assertNewsletterLayoutFileDefined($area, $expectedFileName)
    {
        $this->assertLayoutFileDefined($area, $expectedFileName);
    }

    //  }}}
    //  {{{ assertNewsletterLayoutFileExistsForDefaultTheme()

    /**
     * test
     * @group config
     * @group amd
     * @dataProvider themeLayoutFilesExistProvider
     */
    public function assertNewsletterLayoutFileExistsForDefaultTheme(
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
