<?php

/**
 * API Token Validation Helper
 *
 * @category  Bronto
 * @package   Bronto_Verify
 * @author    Adam Daniels <adam.daniels@atlanticbt.com>
 * @copyright 2013 Adam Daniels
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   0.1.0
 */
class Bronto_Verify_Helper_Apitoken
    extends Bronto_Verify_Helper_Data
{
    /**
     * API Token Status
     * @var integer
     * @access private
     */
    private $_status = 0;

    /**
     * Get API Token Validation Status
     *
     * @return void
     * @access public
     */
    public function __construct()
    {
        $this->_status = Mage::getStoreConfig($this->getPath('token_status'));
    }

    /**
     * Get the overall status of the API Token verification
     *
     * @return string
     * @access public
     */
    public function getApitokenStatus()
    {
        return $this->_status;
    }

    /**
     * Get a formatted version of the API Token status text
     *
     * @return string
     * @access public
     */
    protected function _getApitokenStatusText()
    {
        switch ($this->_status) {
            case 1:
                return '<span id="bronto-validation-status" class="valid">Passed Verification</span>';
                break;
            case 0:
                return '<span id="bronto-validation-status" class="invalid">Failed Verification</span>';
                break;
            default:
                return '<span id="bronto-validation-status" class="">Needs Verification</span>';
                break;
        }
    }

    /**
     * Get a formatted version of the API Token status text scoped to current admin scope
     *
     * @return type
     * @access public
     */
    public function getAdminScopedApitokenStatusText()
    {
        $this->_status = $this->getAdminScopedConfig($this->getPath('token_status'));

        return $this->_getApitokenStatusText();
    }
}
