<?php

/**
 * Roundtrip helper
 *
 * PHP version 5
 *
 * The license text...
 *
 * @category  Bronto
 * @package   Roundtrip
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2011-2012 Bronto Software, Inc.
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   1.6.7
 * @link      <>
 * @see       References to other sections (if any)...
 */


/**
 * Roundtrip helper
 *
 * @category  Bronto
 * @package   Roundtrip
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2011-2012 Bronto Software, Inc.
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   1.6.7
 * @link      <>
 * @see       References to other sections (if any)...
 */
class Bronto_Roundtrip_Helper_Data
    extends Bronto_Common_Helper_Data
{
    //  {{{ properties

    /**
     * Description for const
     */
    const XML_PATH_ROUNDTRIP_ROOT     = 'bronto_roundtrip/settings/';

    /**
     * Description for private
     * @var integer
     * @access private
     */
    private $_status = 0;

    //  }}}
    //  {{{ __construct()

    /**
     * Short description for function
     *
     * Long description (if any) ...
     *
     * @return void
     * @access public
     */
    public function __construct()
    {
        $this->_status = Mage::getStoreConfig(self::XML_PATH_ROUNDTRIP_ROOT . 'status');
    }

    //  }}}
    //  {{{ getRoundtripStatus()

    /**
     * Get the overall status of the roundtrip verification
     *
     * @return string
     * @access public
     */
    public function getRoundtripStatus()
    {
        return $this->_status;
    }

    //  }}}
    //  {{{ getRoundtripStatusText()

	/**
	 * Get a formatted version of the status text
	 *
	 * @return string
     * @access public
	 */
    public function getRoundtripStatusText()
    {
        switch ($this->_status) {
            case 1:
                return '<span style="color:green; font-weight:bold;">Passed Verification</span>';
                break;
            case 0:
                return '<span style="color:red; font-weight:bold;">Failed Verification</span>';
                break;
            default:
                return '<span style="color:grey; font-weight:bold;">Needs Verification</span>';
                break;
        }
    }

    //  }}}
    //  {{{ setRoundtripStatus()

    /**
     * Set the value of a setting
     *
     * @param string $path  The setting path to set the value for
     * @param string $value
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function setRoundtripStatus($path, $value)
    {
        return Mage::getSingleton('core/config')
            ->saveConfig($path, $value);
    }

    //  }}}
    //  {{{ getPath()

    /**
     * Get the full path from path ending
     *
     * @param string $pathend The setting to get the path for
     *
     * @return string
     * @access public
     */
    public function getPath($pathend)
    {
        return self::XML_PATH_ROUNDTRIP_ROOT . $pathend;
    }

    //  }}}
}
