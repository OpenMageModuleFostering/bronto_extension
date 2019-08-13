<?php

/**
 * Array implementation of XML path locator
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
 * Array implementation of XML path locator
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
class Bronto_ConflictChecker_Model_Path_Locator_Array
    extends Bronto_ConflictChecker_Model_Path_Locator_IteratorAbstract
    implements Bronto_ConflictChecker_Model_Path_Locator_LocatorInterface
{
    //  {{{ getPath()

    /**
     * Gets a path to a node via array implementation
     *
     * Pass in the child node and will recurse up the XML tree to print out
     * the path in the tree to that node
     *
     * <config>
     *   <path>
     *     <to>
     *       <node>
     *         Node Value
     *       </node>
     *     </to>
     *   </path>
     * </config>
     *
     * If you pass in the "node" object, this will print out
     * config/path/to/node/
     *
     * @param SimpleXmlElement $element Child element to find path to
     *
     * @return string
     * @access public
     */
    public function getPath(SimpleXmlElement $element)
    {
        $this->_iterator[] = $element->getName() . '/';
        if (!$element->getSafeParent()) {
            return array_pop($this->_iterator);
        }
        return $this->getPath($element->getParent()) . array_pop($this->_iterator);
    }

    //  }}}
}
