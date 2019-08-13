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
 */
class Bronto_Newsletter_Model_Queue extends Mage_Core_Model_Abstract
{

    /**
     * Short description for function
     * 
     * Long description (if any) ...
     * 
     * @return void  
     * @access public
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('bronto_newsletter/queue');
    }
    
    public function getContactRow($subscriber_id, $store_id)
    {
        $collection = $this->getCollection()
             ->addFieldToFilter('subscriber_id', $subscriber_id)
             ->addFieldToFilter('store', $store_id);
        
        if ($collection->count() == 1) {
            return $collection->getFirstItem();
        } else {
            $this->setSubscriberId($subscriber_id)
                 ->setStore($store_id);
        }
        
        return $this;
    }
}