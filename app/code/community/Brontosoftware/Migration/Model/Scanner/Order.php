<?php

class Brontosoftware_Migration_Model_Scanner_Order extends Brontosoftware_Migration_Model_Scanner
{
    const MODULE_PATH = 'bronto_order/%';

    protected $_fieldsToLabel = array(
        'enabled' => 'Enabled',
        'import_states' => 'Orders to Import',
        'delete_states' => 'Orders to Delete',
        'description' => 'Product Description',
        'price' => 'Product Price',
        'incl_tax' => 'Include Tax',
        'incl_shipping' => 'Include Shipping',
        'incl_discount' => 'Include Discount'
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
    protected function _translateValue($section, $key, $value)
    {
        $value = parent::_translateValue($section, $key, $value);
        if ($key == 'import_states' || $key == 'delete_states') {
            $value = explode(',', $value);
        }
        return $value;
    }
}
