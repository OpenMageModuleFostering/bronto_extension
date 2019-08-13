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
            $helper = Mage::helper('bronto_common');

            if ($storeIds = $helper->getStoreIds()) {
                foreach ($storeIds as $storeId) {
                    $storeResult = $model->processSubscribersForStore($storeId);
                    $result['total']   += $storeResult['total'];
                    $result['success'] += $storeResult['success'];
                    $result['error']   += $storeResult['error'];
                }
            } else {
                $result = $model->processSubscribers();
            }

            if (is_array($result)) {
                $this->_getSession()->addSuccess(sprintf("Processed %d Subscribers (%d Error / %d Success)", $result['total'], $result['error'], $result['success']));
            } else {
                $this->_getSession()->addError('Scheduled Sync failed: ' . $result);
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
        $subscribers = Mage::getModel('bronto_newsletter/queue')
            ->getCollection()
            ->addFilter('imported', 1);

        foreach($subscribers as $subscriber) {
            try {
                $subscriber->setImported(0)->save();
            } catch(Exception $e) {
                Mage::helper('bronto_newsletter')->writeError($e);
        }

        
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
