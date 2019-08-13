<?php

/**
 * @package    Order
 * @copyright  2011-2012 Bronto Software, Inc.
 * @version    1.1.5
 * @deprecated
 */
class Bronto_Order_Block_Bta extends Mage_Core_Block_Text
{
    /**
     * Render Bronto tracking script
     *
     * @return string
     */
    protected function _toHtml()
    {
        $siteId = '';
        $host   = '';

        return "
            <script>
            (function(d,t){
                var b=d.createElement(t), s=d.getElementsByTagName(t)[0];
                b.src='//p.bm23.com/bta.js';
                b.onload = function(){ var bta = new __bta('{$siteId}'); bta.setHost('{$host}'); };
                s.parentNode.insertBefore(b,s)
            }(document,'script'));
            </script>";
    }
}
