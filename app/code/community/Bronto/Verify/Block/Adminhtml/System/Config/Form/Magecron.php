<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2013 Bronto Software, Inc.
 */
class Bronto_Verify_Block_Adminhtml_System_Config_Form_Magecron
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return footer html for fieldset
     * Add extra tooltip comments to elements
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getFooterHtml($element)
    {
        $html = "<tr><td>&nbsp;</td>
            <td id=\"bronto-magecron-example\" colspan=\"3\"><strong style=\"margin:5px;\">To setup the cron script, you will need to add a command to your crontab file.  Here are some examples:</strong>
            <div style=\"border:1px solid #ccc; padding:5px; margin:5px;\">
<strong>To run the API Retry Cron every 2 minutes:</strong>
<pre>*/2 * * * * rot /usr/bin/php /var/www/magento/shell/bronto/cron.php -a run -t api</pre>
<strong>To run the Reminder Cron every 15 minutes:</strong>
<pre>*/15 * * * * root /usr/bin/php /var/www/magento/shell/bronto/cron.php -a run -t reminder</pre>
<strong>To run the Order Import Cron once Daily at Midnight:</strong>
<pre>0 0 * * * root /usr/bin/php /var/www/magento/shell/bronto/cron.php -a run -t order</pre>
<strong>To run the Customer Import Cron twice Daily:</strong>
<pre>0 */2 * * * root /usr/bin/php /var/www/magento/shell/bronto/cron.php -a run -t customer</pre>
<strong>To run the Newsletter Opt-In Cron every 30 minutes:</strong>
<pre>*/30 * * * * root /usr/bin/php /var/www/magento/shell/bronto/cron.php -a run -t newsletter</pre>
<strong>To run all Module Crons once Daily:</strong>
<pre>0 0 * * * root /usr/bin/php /var/www/magento/shell/bronto/cron.php -a run</pre>
</div>
<em style=\"margin:5px;\">* Note: You will need to change the owner, php path, and path to magento to match your environment.</em>
</td></tr>";

        return $html . parent::_getFooterHtml($element);
    }
}
