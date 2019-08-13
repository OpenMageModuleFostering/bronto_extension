<?php

/**
 * Datastore printer
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
 * Datastore printer
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
class Bronto_ConflictChecker_Model_Config_Printer
{
    //  {{{ render()


    /**
     * Rewrite printer
     *
     * @param Bronto_ConflictChecker_Model_Config_Datastore $datastore Datastore to print from
     * @param string                                        $title     Title to print

     * @return string
     * @access public
     */
    public function render(
        Bronto_ConflictChecker_Model_Config_Datastore $datastore,
        $title
    ) {
        $block = Mage::app()->getLayout()->createBlock('bronto_conflictchecker/printer');
        $block->setRewrites($datastore->getRewriteConflicts());
        $block->setTitle($title);

        return $block->toHtml();
    }

    //  }}}
}
