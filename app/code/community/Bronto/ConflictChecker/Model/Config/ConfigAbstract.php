<?php

/**
 * Rewrite config checker
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
 * Rewrite config checker
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
abstract class Bronto_ConflictChecker_Model_Config_ConfigAbstract
    extends Mage_Core_Model_Abstract
    implements Bronto_ConflictChecker_Model_Config_ConfigInterface
{
    //  {{{ properties


    /**
     * Chain of Responsibility link
     * @var object
     * @access protected
     */
    protected $_nextHandler = NULL;

    //  }}}
    //  {{{ _construct()


    /**
     * psuedo constructor
     *
     * If a handler is passed into the constructor then set it as the next link
     *
     * @return void
     * @access public
     */
    public function _construct()
    {
        if (isset($this->_data[0])) {
            $this->_nextHandler = $this->_data[0];
        }
    }

    //  }}}
    //  {{{ getRewrites()


    /**
     * Check if there are more handlers and if so get the rewrites from them

     * @param Bronto_ConflictChecker_Model_Core_Config_Element $config   XML node
     * @param array                                            $rewrites existing rewrites

     * @return array  rewrites
     * @access public
     */
    public function getRewrites(
        Bronto_ConflictChecker_Model_Core_Config_Element $config,
        $rewrites = array()
    ) {
        if (!is_null($this->_nextHandler)) {
            return $this->_nextHandler->getRewrites($config, $rewrites);
        } else {
            return $rewrites;
        }
    }

    //  }}}
    //  {{{ _findRewrites()


    /**
     * Find if XML node has any rewrites and if so append them into list
     *
     * @param Bronto_ConflictChecker_Model_Core_Config_Element    $config    XML Node
     * @param array                                               &$rewrites existing rewrites
     *
     * @return void
     * @access protected
     */
    protected function _findRewrites(
        Bronto_ConflictChecker_Model_Core_Config_Element $config,
        &$rewrites = array()
    ) {
        $reflect = new ReflectionObject($config);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            $module    = $prop->getName();
            $reflect = new ReflectionObject($config->$module);
            if ($reflect->hasProperty('rewrite')) {
                $rewrite    = new ReflectionObject($config->$module->rewrite);
                $properties = $rewrite->getProperties(ReflectionProperty::IS_PUBLIC);
                foreach ($properties as $property) {
                    $class = $property->name;
                    $rewrites[$this->_type][$module][$class][]
                        = (string) $config->$module->rewrite->$class;
                }
            }
        }
    }

    //  }}}
}
