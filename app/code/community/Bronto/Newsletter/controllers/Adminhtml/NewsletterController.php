<?php

/**
 * @category   Bronto
 * @package    Bronto_Newsletter
 */
class Bronto_Newsletter_Adminhtml_NewsletterController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Run immediately
     */
    public function runAction()
    {
        $result = array('total' => 0, 'success' => 0, 'error' => 0);
        $model = Mage::getModel('bronto_newsletter/observer');
        $helper = Mage::helper('bronto_newsletter');
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
                    $storeResult = $model->processSubscribersForStore($storeId, $limit);
                    $result['total'] += $storeResult['total'];
                    $result['success'] += $storeResult['success'];
                    $result['error'] += $storeResult['error'];
                    $limit = $limit - $storeResult['total'];
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
            $helper->writeError($e);
        }

        $returnParams = array('section' => 'bronto_newsletter');
        $returnParams = array_merge($returnParams, $helper->getScopeParams());
        $this->_redirect('*/system_config/edit', $returnParams);
    }

    /**
     * Reset all Subscribers
     */
    public function resetAction()
    {
        $helper = Mage::helper('bronto_newsletter');
        $resource = Mage::getResourceModel('bronto_newsletter/queue');
        $adapter = $resource->getWriteAdapter();

        try {
            $adapter->update(
                $resource->getTable('bronto_newsletter/queue'),
                array(
                    'imported'          => 2,
                    'bronto_suppressed' => null,
                ),
                array('imported' => 1)
            );
        } catch (Exception $e) {
            $helper->writeError($e);
            $this->_getSession()->addError('Reset failed: ' . $e->getMessage());
        }

        $returnParams = array('section' => 'bronto_newsletter');
        $returnParams = array_merge($returnParams, $helper->getScopeParams());
        $this->_redirect('*/system_config/edit', $returnParams);
    }

    /**
     * Pull Subscribers from Subscribers Table if not in queue
     */
    public function syncAction()
    {
        $helper = Mage::helper('bronto_newsletter');
        $imported = 0;

        try {
            $subscribers = $helper->getMissingSubscribers();
            $waiting = count($subscribers);

            if ($waiting > 0) {
                foreach ($subscribers as $subscriber) {
                    // Convert Magento subscriber status to bronto subscriber status
                    switch ($subscriber['subscriber_status']) {
                        case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                            $status = Bronto_Api_Contact::STATUS_ACTIVE;
                            break;

                        case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                            $status = Bronto_Api_Contact::STATUS_UNSUBSCRIBED;
                            break;

                        case Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED:
                            $status = Bronto_Api_Contact::STATUS_UNCONFIRMED;
                            break;

                        case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
                        default:
                            $status = Bronto_Api_Contact::STATUS_TRANSACTIONAL;
                            break;
                    }

                    // Create Subscriber
                    Mage::getModel('bronto_newsletter/queue')->getContactRow($subscriber['subscriber_id'], $subscriber['store_id'])
                        ->setStatus($status)
                        ->setSubscriberEmail($subscriber['subscriber_email'])
                        ->setMessagePreference('html')
                        ->setSource('api')
                        ->setImported(0)
                        ->setBrontoSuppressed(NULL)
                        ->save();

                    $imported++;
                }
            }
        } catch (Exception $e) {
            $helper->writeError($e);
            $this->_getSession()->addError('Sync failed: ' . $e->getMessage());
        }

        $this->_getSession()->addSuccess(sprintf("%d of %d Subscribers were added to the Queue", $imported, $waiting));

        $returnParams = array('section' => 'bronto_newsletter');
        $returnParams = array_merge($returnParams, $helper->getScopeParams());
        $this->_redirect('*/system_config/edit', $returnParams);
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
