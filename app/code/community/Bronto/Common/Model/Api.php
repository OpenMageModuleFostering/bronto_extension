<?php

/**
 * @category Bronto
 * @package Common
 */
class Bronto_Common_Model_Api extends Bronto_Api
{
    //  {{{ properties

    /**
     * @var array
     */
    static private $_instances = array();

    //  }}}
    //  {{{ getInstance()

    /**
     * @param string $token
     * @param bool $debug
     *
     * @return Bronto_Common_Model_Api
     * @access public
     */
    public static function getInstance($token, $debug = true)
    {
        $token = trim($token);

        if (!isset(self::$_instances[$token])) {
            Mage::helper('bronto_common')->writeDebug("Initiating API for token: {$token}");
            self::$_instances[$token] = new self($token, array(
                'retry_limit' => 2,
                'debug' => $debug,
            ));
        }

        return self::$_instances[$token];
    }

    //  }}}
    //  {{{ throwException()

    /**
     * @param string|Exception $exception
     * @param string $message
     * @param string $code
     *
     * @return void
     * @access public
     * @throws Bronto_Api_Exception
     */
    public function throwException($exception, $message = null, $code = null)
    {
        try {
            parent::throwException($exception, $message, $code);
        } catch (Bronto_Api_Exception $e) {
            if ($request = $e->getRequest()) {
                Mage::helper('bronto_common')->writeDebug(var_export($request, true));
            }
            if ($response = $e->getResponse()) {
                Mage::helper('bronto_common')->writeDebug(var_export($response, true));
            }
            throw $e;
        }
    }

    //  }}}
}
