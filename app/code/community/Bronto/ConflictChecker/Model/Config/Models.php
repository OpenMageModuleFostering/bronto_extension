<?php

/**
 * Model config checker
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
 * Model config checker
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
class Bronto_ConflictChecker_Model_Config_Models
    extends Bronto_ConflictChecker_Model_Config_ConfigAbstract
{
    //  {{{ properties


    /**
     * Type of rewrite
     * @var string
     * @access protected
     */
    protected $_type = 'models';

    //  }}}
    //  {{{ getRewrites()

    /**
     * Check models section for rewrites
     *
     * @param Bronto_ConflictChecker_Model_Core_Config_Element $config   Config node
     * @param array                                            $rewrites Existing rewrites
     *
     * @return array rewrites
     * @access public
     */
    public function getRewrites(
        Bronto_ConflictChecker_Model_Core_Config_Element $config,
        $rewrites = array()
    ) {
        $models = $config->models;
        $this->_findRewrites($models, $rewrites);

        return parent::getRewrites($config, $rewrites);
    }

    //  }}}
}
