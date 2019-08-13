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
        if (!Mage::helper('bronto_order')->isModuleEnabled()) {
            return;
        }

        return "
<script type=\"text/javascript\">
	document.write(unescape(\"%3Cscript src='\"
		+ ((document.location.protocol == \"https:\") ? \"https:\" : \"http:\")
		+ \"//p.bm23.com/bta.js' type='text/javascript'%3E%3C/script%3E\"));
</script>
";
    }
}
