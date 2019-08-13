<?php

class Brontosoftware_Connector_Model_Impl_Core_Logger implements Brontosoftware_Magento_Core_Log_LoggerInterface
{
    const LOG_FILE = 'brontosoftware_connector.log';

    /**
     * @see parent
     */
    public function debug($message, array $context = array())
    {
        Mage::log($message, Zend_Log::DEBUG, self::LOG_FILE);
    }

    /**
     * @see parent
     */
    public function info($message, array $context = array())
    {
        Mage::log($message, Zend_Log::INFO, self::LOG_FILE, true);
    }

    /**
     * @see parent
     */
    public function critical($message, array $context = array())
    {
        if ($message instanceof Exception) {
            Mage::logException($message);
        } else {
            Mage::log($message, Zend_Log::ERR, self::LOG_FILE, true);
        }
    }
}
