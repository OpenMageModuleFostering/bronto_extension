<?php

class Brontosoftware_Migration_Model_Scanner_Product extends Brontosoftware_Migration_Model_Scanner
{
    const MODULE_PATH = 'bronto_product/%';

    protected $_fieldsToLabel = array(
        'enabled' => 'Enabled',
        'description' => 'Product Description',
        'char_limit' => 'Description Length',
    );

    /**
     * @see parent
     */
    protected function _modulePath()
    {
        return self::MODULE_PATH;
    }

    /**
     * @see parent
     */
    protected function _afterConfig($settings)
    {
        $settings = parent::_afterConfig($settings);
        $recommendations = Mage::getModel('bronto_product/recommendation')
            ->getCollection()
            ->addFieldToFilter('content_type', array('eq' => 'api'))
            ->addFieldToFilter('store_id', array('eq' => $this->_scopeId));
        foreach ($recommendations as $recommendation) {
            if (!array_key_exists('recommendations', $settings)) {
                $settings['recommendations'] = array();
            }
            $settings['recommendations'][] = array_filter($recommendation->getData(), array($this, '_filterNulls'));
        }
        return $settings;
    }
}
