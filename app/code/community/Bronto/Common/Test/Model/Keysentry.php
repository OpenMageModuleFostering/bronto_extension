<?php

class Bronto_Common_Test_Model_Keysentry extends EcomDev_PHPUnit_Test_Case
{
    public function configDataProvider()
    {
        $data = array(
            'field' => 'api_token',
            'group_id' => 'settings',
            'store_code' => '',
            'website_code' => '',
            'scope' => 'default',
            'scope_id' => 0,
            'fieldset_data' =>  array(
                'api_token' => '',
                'debug' => '',
                'verbose' => '',
                'test' => '',
                'notices' => '',
            ),
            'path' => 'bronto/settings/api_token',
            'value' => '53873730-F77B-4B0D-9840-43F21846F991',
        );
        $defaultConfig = Mage::getModel('bronto_common/system_config_backend_token');
        $defaultConfig->setData($data);

        $data['website_code'] = 'base';
        $data['scope'] = 'websites';
        $data['scope_id'] = 1;
        $websiteConfig = Mage::getModel('bronto_common/system_config_backend_token');
        $websiteConfig->setData($data);

        $data['store_code'] = 'default';
        $data['scope'] = 'stores';
        $data['scope_id'] = 1;
        $englishConfig = Mage::getModel('bronto_common/system_config_backend_token');
        $englishConfig->setData($data);

        $data['store_code'] = 'french';
        $data['scope'] = 'stores';
        $data['scope_id'] = 3;
        $frenchConfig = Mage::getModel('bronto_common/system_config_backend_token');
        $frenchConfig->setData($data);

        $data['store_code'] = 'german';
        $data['scope'] = 'stores';
        $data['scope_id'] = 2;
        $germanConfig = Mage::getModel('bronto_common/system_config_backend_token');
        $germanConfig->setData($data);

        return array(
            array($defaultConfig),
            array($websiteConfig),
            array($englishConfig),
            array($frenchConfig),
            array($germanConfig),
        );
    }

    /**
     * @test
     * @group jmk
     * @group model
     * @dataProvider configDataProvider
     */
    public function getMatchingScopes($configuration)
    {
        $config = Mage::getStoreConfig('bronto/settings/api_token');
    }
}
