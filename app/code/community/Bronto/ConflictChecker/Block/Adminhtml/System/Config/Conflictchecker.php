<?php

/**
 * Conflict checker
 *
 * This is the heart of the conflict checker that glues together and fires
 * the Chain of responsibility
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
 * Conflict checker
 *
 * This is the heart of the conflict checker that glues together and fires
 * the Chain of responsibility
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
class Bronto_ConflictChecker_Block_Adminhtml_System_Config_Conflictchecker
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    //  {{{ properties


    /**
     * rewritten xml nodes
     * @var array
     * @access protected
     */
    protected $_rewrittenConfigs = array();

    //  }}}
    //  {{{ render()


    /**
     * Render all xml names that conflict
     *
     * @param Varien_Data_Form_Element_Abstract $element Form element
     *
     * @return string
     * @access public
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $globalDataStore = Mage::getModel('bronto_conflictchecker/config_datastore');
        Mage::register('conflict_datastore', $globalDataStore);
        $config = Mage::getModel('bronto_conflictchecker/core_config');
        $config->reinit();

        //  Chain of Responsibility
        //  each checker looks through its designated area for rewrites
        $blocks    = Mage::getModel('bronto_conflictchecker/config_blocks');
        $models    = Mage::getModel('bronto_conflictchecker/config_models', array($blocks));
        $helpers   = Mage::getModel('bronto_conflictchecker/config_helpers', array($models));
        $resources = Mage::getModel('bronto_conflictchecker/config_resources', array($helpers));
        $checker   = Mage::getModel('bronto_conflictchecker/config_checker', array($resources));

        $conflicts = $checker->getConflicts($config->getNode('frontend'));

        $globalDataStore->getRewriteConflicts();

        $printer = new Bronto_ConflictChecker_Model_Config_Printer();
        return $printer->render($globalDataStore, 'XML configurations rewritten more than once');
    }

    //  }}}
}
