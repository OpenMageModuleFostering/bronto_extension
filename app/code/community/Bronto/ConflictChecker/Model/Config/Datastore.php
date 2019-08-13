<?php

/**
 * Short description for file
 *
 * Long description (if any) ...
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
 * Short description for class
 *
 * Long description (if any) ...
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
class Bronto_ConflictChecker_Model_Config_Datastore
    extends Mage_Core_Model_Abstract
{
    //  {{{ properties


    /**
     * data store
     * @var array
     * @access protected
     */
    protected $_store = array();

    //  }}}
    //  {{{ addRewrite()


    /**
     * store rewrite
     *
     * @param string      $oldValue   node name being overwritten
     * @param string      $newValue   node name that is being set to current
     * @param string|null $configFile (optional) Config file with rewrite
     * @param string|null $path       (optional) path to node in XML
     *
     * @return void
     * @access public
     */
    public function addRewrite(
        $oldValue,
        $newValue,
        $configFile = 'Unavailable',
        $path = 'Unavailable'
    ) {
        if ('Unavailable' != $configFile) {
            //  +1 just removes the starting '/' from the path
            $configFile = substr($configFile, strlen(Mage::getBaseDir()) + 1, strlen($configFile));
        }
        $this->_store[] = array(
            'oldValue' => $oldValue,
            'newValue' => $newValue,
            'file'    => $configFile,
            'path'     => $path
        );
    }

    //  }}}
    //  {{{ getRewriteConflicts()


    /**
     * Get the datastore
     *
     * @return array
     * @access public
     */
    public function getRewriteConflicts()
    {
        return $this->_store;
    }

    //  }}}
}
