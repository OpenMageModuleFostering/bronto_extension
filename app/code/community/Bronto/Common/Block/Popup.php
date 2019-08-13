<?php

/**
 * @package    Bronto/Common
 * @copyright  2011-2013 Bronto Software, Inc.
 */
class Bronto_Common_Block_Popup extends Mage_Core_Block_Template
{
    /**
     * Get Pop-Up Javascript
     *
     * @return mixed
     */
    public function getPopupCode()
    {
        return Mage::helper('bronto_common')->getPopupCode();
    }
}
