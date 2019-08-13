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
 * @package   Newsletter
 * @author    Jeff Lambert <jeff.lambert@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT 
 * @version   CVS: $Id:$
 * @link      <>
 * @see       References to other sections (if any)...
 */
/**
 * @author Jeff Lambert <jeff.lambert@atlanticbt.com>
 *         */
class Bronto_Newsletter_Model_Mysql4_Queue_Collection 
	extends Mage_Core_Model_Mysql4_Collection_Abstract
{    
    /**
     * Short description for function
     * 
     * Long description (if any) ...
     * 
     * @return void  
     * @access public
     */
    public function _construct() {
        parent::_construct();
        $this->_init('bronto_newsletter/queue');
    }
    
    
    
    /**
     * @return Bronto_Newsletter_Model_Mysql4_Queue_Collection
     */
    public function addBrontoImportedFilter()
    {
        $this->addFieldToFilter('imported', array('eq' => '1'));
        return $this;
    }

    /**
     * @return Bronto_Newsletter_Model_Mysql4_Queue_Collection
     */
    public function addBrontoNotImportedFilter()
    {
        $this->addFieldToFilter('imported', array('eq' => '0'));
        return $this;
    }

    /**
     * @param mixed $storeIds (null, int|string, array, array may contain null)
     * @return Bronto_Newsletter_Model_Mysql4_Queue_Collection
     */
    public function addStoreFilter($storeIds)
    {
        $nullCheck = false;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        $storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];

        if ($nullCheck) {
            $this->getSelect()->where('store IN(?) OR store IS NULL', $storeIds);
        } else {
            $this->getSelect()->where('store IN(?)', $storeIds);
        }

        return $this;
    }
}