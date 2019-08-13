<?php

/**
 * @category Bronto
 * @package Order
 */
class Bronto_Order_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Run immediately
     */
    public function runAction()
    {
        try {
            $result = array('total' => 0, 'success' => 0, 'error' => 0);
            $model  = Mage::getModel('bronto_order/observer');
            $helper = Mage::helper('bronto_order');
            
            if ($storeIds = $helper->getStoreIds()) {
                foreach ($storeIds as $storeId) {
                    $storeResult = $model->processOrdersForStore($storeId);
                    $result['total']   += $storeResult['total'];
                    $result['success'] += $storeResult['success'];
                    $result['error']   += $storeResult['error'];
                }
            } else {
                $result = $model->processOrders();
            }

            if (is_array($result)) {
                $this->_getSession()->addSuccess(sprintf("Processed %d Orders (%d Error / %d Success)", $result['total'], $result['error'], $result['success']));
            } else {
                $this->_getSession()->addError('Scheduled Import failed: ' . $result);
            }

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::helper('bronto_order')->writeError($e);
        }

        $this->_redirect('*/system_config/edit', array('section' => 'bronto_order'));
    }

    /**
     * Reset all Orders
     */
    public function resetAction()
    {
        $helper    = Mage::helper('bronto_order');
        $storeIds  = $helper->getStoreIds();
        
        $collection = Mage::getModel('bronto_order/queue')->getCollection();

        if ($storeIds) {
            $collection->addStoreFilter($storeIds);
        }
        
        foreach ($collection->getItems() as $orderRow) {
            try {
                $orderRow->setBrontoImported(null)->save();
            } catch (Exception $e) {
                Mage::helper('bronto_order')->writeError($e);
                $this->_getSession()->addError('Reset failed: ' . $e->getMessage());
            }
        }
        
        $this->_redirect('*/system_config/edit', array('section' => 'bronto_order'));
    }
    
    /**
     * Pull Orders from Order Table if not in queue
     */
    public function syncAction()
    {
        $imported = 0;
        $waiting  = 0;
        
        try {
            $orders = Mage::helper('bronto_order')->getMissingOrders();             
            $waiting   = $orders->count();
            
            if ($waiting > 0) {
                foreach ($orders as $order) {
                    Mage::getModel('bronto_order/queue')->getOrderRow($order->getEntityId(), null, $order->getStoreId())
                        ->setQuoteId($order->getQuoteId())
                        ->setCreatedAt($order->getCreatedAt())
                        ->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate())
                        ->setBrontoImported(0)
                        ->save();

                    $imported++;
                }
            }
        } catch (Exception $e) {
            Mage::helper('bronto_order')->writeError($e);
            $this->_getSession()->addError('Sync failed: ' . $e->getMessage());
        }
        
        $this->_getSession()->addSuccess(sprintf("%d of %d Orders were added to the Queue", $imported, $waiting));
        $this->_redirect('*/system_config/edit', array('section' => 'bronto_order'));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_isSectionAllowed('bronto_order');
    }

    /**
     * Check if specified section allowed in ACL
     *
     * Will forward to deniedAction(), if not allowed.
     *
     * @param string $section
     * @return bool  
     */
    protected function _isSectionAllowed($section)
    {
        try {
            $session = Mage::getSingleton('admin/session');
            $resourceLookup = "admin/system/config/{$section}";
            if ($session->getData('acl') instanceof Mage_Admin_Model_Acl) {
                $resourceId = $session->getData('acl')->get($resourceLookup)->getResourceId();
                if (!$session->isAllowed($resourceId)) {
                    throw new Exception('');
                }
                return true;
            }
        } catch (Zend_Acl_Exception $e) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (Exception $e) {
            $this->deniedAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
    }
}
