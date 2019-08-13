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
        $result = array('total' => 0, 'success' => 0, 'error' => 0);
        $model = Mage::getModel('bronto_customer/observer');
        $helper = Mage::helper('bronto_customer');
        $limit = $helper->getLimit();

        try {
            if ($storeIds = $helper->getStoreIds()) {
                if (!is_array($storeIds)) {
                    $storeIds = array($storeIds);
                }
                foreach ($storeIds as $storeId) {
                    if ($limit <= 0) {
                        continue;
                    }
                    $storeResult = $model->processCustomersForStore($storeId, $limit);
                    $result['total'] += $storeResult['total'];
                    $result['success'] += $storeResult['success'];
                    $result['error'] += $storeResult['error'];
                    $limit = $limit - $storeResult['total'];
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
            $helper->writeError($e);
        }

        $returnParams = array('section' => 'bronto_customer');
        $returnParams = array_merge($returnParams, $helper->getScopeParams());
        $this->_redirect('*/system_config/edit', $returnParams);
    }

    /**
     * Reset all Customers
     */
    public function resetAction()
    {
        $helper = Mage::helper('bronto_customer');
        $storeIds = $helper->getStoreIds();
        $resource = Mage::getResourceModel('bronto_customer/queue');
        $adapter = $resource->getWriteAdapter();

        $where = array();
        if ($storeIds) {
            $where = array('store_id IN (?)' => $storeIds);
        }

        try {
            $adapter->update(
                $resource->getTable('bronto_customer/queue'),
                array(
                    'bronto_imported' => null,
                    'bronto_suppressed' => null,
                ),
                $where
            );
        } catch (Exception $e) {
            $helper->writeError($e);
            $this->_getSession()->addError('Reset failed: ' . $e->getMessage());
        }

        $returnParams = array('section' => 'bronto_customer');
        $returnParams = array_merge($returnParams, $helper->getScopeParams());
        $this->_redirect('*/system_config/edit', $returnParams);
    }

    /**
     * Pull Customers from Customer Table if not in queue
     */
    public function syncAction()
    {
        $helper = Mage::helper('bronto_customer');
        $imported = 0;

        try {
            $customers = Mage::helper('bronto_customer')->getMissingCustomers();
            $waiting = count($customers);

            if ($waiting > 0) {
                foreach ($customers as $customer) {
                    Mage::getModel('bronto_customer/queue')->getCustomerRow($customer['entity_id'], $customer['store_id'])
                        ->setCreatedAt($customer['created_at'])
                        ->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate())
                        ->setBrontoImported(0)
                        ->save();

                    $imported++;
                }
            }
        } catch (Exception $e) {
            Mage::helper('bronto_customer')->writeError($e);
            $this->_getSession()->addError('Sync failed: ' . $e->getMessage());
        }

        $this->_getSession()->addSuccess(sprintf("%d of %d Customers were added to the Queue", $imported, $waiting));
        $returnParams = array('section' => 'bronto_customer');
        $returnParams = array_merge($returnParams, $helper->getScopeParams());
        $this->_redirect('*/system_config/edit', $returnParams);
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
