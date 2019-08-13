<?php

/**
 * Block config checker
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
 * Block config checker
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
class Bronto_ConflictChecker_Model_Config_Blocks
    extends Bronto_ConflictChecker_Model_Config_ConfigAbstract
{
    //  {{{ properties


    /**
     * Type of rewrite
     * @var string
     * @access protected
     */
    protected $_type = 'blocks';

    //  }}}
    //  {{{ getRewrites()


    /**
     * Check block section for rewrites
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
        $blocks = $config->blocks;
        $this->_findRewrites($blocks, $rewrites);

        return parent::getRewrites($config, $rewrites);
    }

    //  }}}
}
