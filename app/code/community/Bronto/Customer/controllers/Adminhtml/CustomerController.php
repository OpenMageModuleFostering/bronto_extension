<?php

/**
 * @category   Bronto
 * @package    Bronto_Customer
 */
class Bronto_Customer_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Run immediately
     */
    public function runAction()
    {
        try {
            $result = array('total' => 0, 'success' => 0, 'error' => 0);
            $model  = Mage::getModel('bronto_customer/observer');
            $helper = Mage::helper('bronto_customer');

            if ($storeIds = $helper->getStoreIds()) {
                foreach ($storeIds as $storeId) {
                    $storeResult = $model->processCustomersForStore($storeId);
                    $result['total']   += $storeResult['total'];
                    $result['success'] += $storeResult['success'];
                    $result['error']   += $storeResult['error'];
                }
            } else {
                $result = $model->processCustomers();
            }

            if (is_array($result)) {
                $this->_getSession()->addSuccess(sprintf("Processed %d Customers (%d Error / %d Success)", $result['total'], $result['error'], $result['success']));
            } else {
                $this->_getSession()->addError('Scheduled Import failed: ' . $result);
            }

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::helper('bronto_customer')->writeError($e);
        }

        $this->_redirect('*/system_config/edit', array('section' => 'bronto_customer'));
    }

    /**
     * Reset all Customers
     */
    public function resetAction()
    {
        $helper   = Mage::helper('bronto_customer');
        $storeIds = $helper->getStoreIds();
        
        $collection = Mage::getModel('bronto_customer/queue')->getCollection();

        if ($storeIds) {
            $collection->addStoreFilter($storeIds);
        }
        
        foreach ($collection->getItems() as $customerRow) {
            try {
                $customerRow->setBrontoImported(null)->setBrontoSuppressed(null)->save();
            } catch (Exception $e) {
                Mage::helper('bronto_customer')->writeError($e);
                $this->_getSession()->addError('Reset failed: ' . $e->getMessage());
            }
        }

        $this->_redirect('*/system_config/edit', array('section' => 'bronto_customer'));
    }
    
    /**
     * Pull Customers from Customer Table if not in queue
     */
    public function syncAction()
    {
        $imported = 0;
        $waiting  = 0;
        
        try {
            $customers = Mage::helper('bronto_customer')->getMissingCustomers();             
            $waiting   = $customers->count();
            
            if ($waiting > 0) {
                foreach ($customers as $customer) {
                    Mage::getModel('bronto_customer/queue')->getCustomerRow($customer->getEntityId(), $customer->getStoreId())
                        ->setCreatedAt($customer->getCreatedAt())
                        ->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate())
                        ->setBrontoImported($customer->getBrontoImported())
                        ->save();

                    $imported++;
                }
            }
        } catch (Exception $e) {
            Mage::helper('bronto_customer')->writeError($e);
            $this->_getSession()->addError('Sync failed: ' . $e->getMessage());
        }
        
        $this->_getSession()->addSuccess(sprintf("%d of %d Customers were added to the Queue", $imported, $waiting));
        $this->_redirect('*/system_config/edit', array('section' => 'bronto_customer'));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_isSectionAllowed('bronto_customer');
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
