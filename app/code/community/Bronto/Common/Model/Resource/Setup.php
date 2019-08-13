<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 */
class Bronto_Common_Model_Resource_Setup extends Mage_Sales_Model_Mysql4_Setup
{
    public function handleOld()
    {
        // Look if Bronto folder exists in local codepool and recursively remove if it is
        $source      = Mage::getBaseDir('base') . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Bronto' . DS;
        $destination = Mage::getBaseDir('base') . DS . 'var' . DS . 'bronto_backup' . DS;
        if (file_exists($source)) {
            $this->rcopy($source, $destination);
            $this->rrmdir($source);

            // Add Notification so customer is sure to know
            Mage::getSingleton('adminnotification/inbox')->add(
                4,
                'Bronto Update - Old Version Moved',
                'Bronto has been updated.  We have moved the files from your previous installation to ' . $destination
            );
        }
    }

    public function rrmdir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rrmdir("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    public function rcopy($src, $dst)
    {
        // Remove Destination if it is a file
        if (file_exists($dst)) {
            $this->rrmdir($dst);
        }
        // If Source is a directory create destination and move everything
        if (is_dir($src)) {
            mkdir($dst);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $this->rcopy("$src/$file", "$dst/$file");
                }
            }
        } elseif (file_exists($src)) {
            copy($src, $dst);
        }
    }
}
