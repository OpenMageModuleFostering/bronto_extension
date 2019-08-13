<?php

/**
 * @category   Bronto
 * @package    Bronto_Customer
 */
class Bronto_Newsletter_Adminhtml_NewsletterController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Run immediately
     */
    public function runAction()
    {
        try {
            $result = array('total' => 0, 'success' => 0, 'error' => 0);
            $model  = Mage::getModel('bronto_newsletter/observer');

            if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
                $store      = Mage::app()->getStore($storeCode);
                $storeIds[] = $store->getId();
            } else if ($websiteCode = Mage::app()->getRequest()->getParam('website')){
                $website  = Mage::app()->getWebsite($websiteCode);
                $storeIds = $website->getStoreIds();
            } else if ($groupCode = Mage::app()->getRequest()->getParam('group')){
                $website  = Mage::app()->getGroup($groupCode)->getWebsite();
                $storeIds = $website->getStoreIds();
            } else {
                $storeIds = false;
            }

            if ($storeIds) {
                foreach ($storeIds as $storeId) {
                    // $storeResult = $model->processCustomersForStore($storeId);
                    $result['total']   += $storeResult['total'];
                    $result['success'] += $storeResult['success'];
                    $result['error']   += $storeResult['error'];
                }
            } else {
                // $result = $model->processCustomers();
            }

            if (is_array($result)) {
                $this->_getSession()->addSuccess(sprintf("Processed %d Subscribers (%d Error / %d Success)", $result['total'], $result['error'], $result['success']));
            } else {
                $this->_getSession()->addError('Scheduled Import failed: ' . $result);
            }

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::helper('bronto_newsletter')->writeError($e);
        }

        $this->_redirect('*/system_config/edit', array('section' => 'bronto_newsletter'));
    }

    /**
     * Reset all Customers
     */
    public function resetAction()
    {
        $storeIds        = array();
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix     = Mage::getConfig()->getTablePrefix();

        if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
            $store      = Mage::app()->getStore($storeCode);
            $storeIds[] = $store->getId();
        } else if ($websiteCode = Mage::app()->getRequest()->getParam('website')){
            $website  = Mage::app()->getWebsite($websiteCode);
            $storeIds = $website->getStoreIds();
        } else if ($groupCode = Mage::app()->getRequest()->getParam('group')){
            $website  = Mage::app()->getGroup($groupCode)->getWebsite();
            $storeIds = $website->getStoreIds();
        } else {
            $storeIds[] = null;
        }

        foreach ($storeIds as $storeId) {
            //
        }

        $this->_redirect('*/system_config/edit', array('section' => 'bronto_newsletter'));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_isSectionAllowed('bronto_newsletter');
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
