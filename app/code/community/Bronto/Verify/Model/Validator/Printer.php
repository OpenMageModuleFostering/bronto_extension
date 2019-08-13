<?php

/**
 * Render the block view
 *
 * @category  Bronto
 * @package   Bronto_Verify
 * @author    Adam Daniels <adam.daniels@atlanticbt.com>
 * @copyright 2013 Adam Daniels
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   0.1.0
 */
class Bronto_Verify_Model_Validator_Printer
{
    /**
     * Render block view
     *
     * @param array $errors bad files to print
     *
     * @return string
     * @access public
     */
    public function render(array $errors)
    {
        $block = Mage::app()->getLayout()->createBlock('bronto_verify/permissionprinter');
        $block->setErrors($errors);
        return $block->toHtml();
    }
}
