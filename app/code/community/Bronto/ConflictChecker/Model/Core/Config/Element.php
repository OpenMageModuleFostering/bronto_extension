<?php

/**
 * XML Configuation element
 *
 * PHP version 5
 *
 * The license text...
 *
 * @category  Bronto
 * @package   ConflictChecker
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   CVS: $Id:$
 * @link      <>
 * @see       References to other sections (if any)...
 */

/**
 * XML Configuation element
 *
 * @category  Bronto
 * @package   ConflictChecker
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   Release: @package_version@
 * @link      <>
 * @see       References to other sections (if any)...
 */
class Bronto_ConflictChecker_Model_Core_Config_Element
    extends Bronto_ConflictChecker_Model_Lib_Varien_Simplexml_Element
{
    //  {{{ is()

    /**
     * Is element enabled
     *
     * @param string  $var
     * @param boolean $value
     *
     * @return boolean
     * @access public
     */
    public function is($var, $value = true)
    {
        $flag = $this->$var;

        if ($value === true) {
            $flag = strtolower((string)$flag);
            if (!empty($flag) && 'false' !== $flag && 'off' !== $flag) {
                return true;
            } else {
                return false;
            }
        }

        return !empty($flag) && (0 === strcasecmp($value, (string)$flag));
    }

    //  }}}
    //  {{{ getClassName()

    /**
     * Get node class name
     *
     * @return string
     * @access public
     */
    public function getClassName()
    {
        if ($this->class) {
            $model = (string)$this->class;
        } elseif ($this->model) {
            $model = (string)$this->model;
        } else {
            return false;
        }
        return Mage::getConfig()->getModelClassName($model);
    }

    //  }}}
}
