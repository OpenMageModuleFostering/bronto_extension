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

            if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
                $store      = Mage::app()->getStore($storeCode);
                $storeIds[] = $store->getId();
            } elseif ($websiteCode = Mage::app()->getRequest()->getParam('website')){
                $website  = Mage::app()->getWebsite($websiteCode);
                $storeIds = $website->getStoreIds();
            } elseif ($groupCode = Mage::app()->getRequest()->getParam('group')){
                $website  = Mage::app()->getGroup($groupCode)->getWebsite();
                $storeIds = $website->getStoreIds();
            } else {
                $storeIds = false;
            }

            if ($storeIds) {
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
        $storeIds        = array();
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix     = Mage::getConfig()->getTablePrefix();

        if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
            $store      = Mage::app()->getStore($storeCode);
            $storeIds[] = $store->getId();
        } elseif ($websiteCode = Mage::app()->getRequest()->getParam('website')){
            $website  = Mage::app()->getWebsite($websiteCode);
            $storeIds = $website->getStoreIds();
        } elseif ($groupCode = Mage::app()->getRequest()->getParam('group')){
            $website  = Mage::app()->getGroup($groupCode)->getWebsite();
            $storeIds = $website->getStoreIds();
        } else {
            $storeIds[] = null;
        }

        foreach ($storeIds as $storeId) {
            $sql = "
                UPDATE {$tablePrefix}sales_flat_order
                SET bronto_imported = null
            ";
            if (!empty($storeId)) {
                $storeId = (int) $storeId;
                $sql    .= " WHERE store_id = {$storeId}";
            }

            try {
                $writeConnection->query($sql);
            } catch (Exception $e) {
                Mage::helper('bronto_order')->writeError($e);
                $this->_getSession()->addError('Reset failed: ' . $e->getMessage());
            }
        }

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
