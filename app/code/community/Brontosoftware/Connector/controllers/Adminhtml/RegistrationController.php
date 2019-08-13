<?php

class Brontosoftware_Connector_Adminhtml_RegistrationController extends Mage_Adminhtml_Controller_Action
{
    const ID_PARAM = 'id';
    const ENTITYID_PARAM = 'entity_id';

    private $_logger;
    private $_middleware;

    /**
     * @see parent
     */
    protected function _construct()
    {
        $this->_logger = Mage::getModel('brontosoftware_connector/impl_core_logger');
        $this->_middleware = Mage::getModel('brontosoftware_connector/impl_connector_middleware');
    }

    /**
     * Initiate the Registration model
     *
     * @param string $idParam
     * @return Brontosoftware_Connector_Model_Registration
     */
    protected function _initModel($idParam = self::ID_PARAM)
    {
        $model = Mage::getModel('brontosoftware_connector/registration');
        if ($this->getRequest()->has($idParam)) {
            $model->load($this->getRequest()->getParam($idParam));
        }
        return $model;
    }

    /**
     * Loads the registration grid
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/brontosoftware_connector')
            ->renderLayout();
    }

    /**
     * Loads the registration form
     *
     * @return void
     */
    public function newAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/brontosoftware_connector')
            ->renderLayout();
    }

    /**
     * Loads the registration form
     *
     * @return void
     */
    public function editAction()
    {
        $this->_forward('new');
    }

    /**
     * Unregisters and deletes the registration
     *
     * @return void
     */
    public function deleteAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        try {
            $model = $this->_initModel();
            if ($this->_middleware->deregister($model)) {
                $model->delete();
            }
            $session->addSuccess($this->__('Successfully deleted the registration.'));
        } catch (Exception $e) {
            $session->addError($this->__('Failed to delete registration.'));
            $this->_logger->critical($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Syncs the settings from the Middleware
     *
     * @return void
     */
    public function syncAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        try {
            $model = $this->_initModel();
            if ($model->hasEntityId()) {
                if (!$this->_middleware->sync($model)) {
                    throw new RuntimeException('Failed to sync.');
                }
            }
            $session->addSuccess($this->__('Successfully synced registration settings.'));
        } catch (Exception $e) {
            $session->addError($this->__('Failed to sync registration settings.'));
            $this->_logger->critical($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Saves the registration on the Middleware and locally
     *
     * @return void
     */
    public function saveAction()
    {
        $model = Mage::getModel('brontosoftware_connector/registration')->setIsActive(false);
        if ($this->getRequest()->getParam('entity_id', null)) {
            $model->load($this->getRequest()->getParam('entity_id'));
        }

        $session = Mage::getSingleton('adminhtml/session');
        try {
            if (!$this->getRequest()->getParam('connector_key')) {
                throw new InvalidArgumentException($this->__("Account ID is required."));
            }

            if (!$this->getRequest()->has('scopeHash')) {
                throw new InvalidArgumentException($this->__("Select a root scope."));
            }

            if (!$this->_middleware->deregister($model)) {
                throw new RuntimeException(__('Failed to unregister ' . $model->getName()));
            }

            $model->setName($this->getRequest()->getParam('name'));
            $model->setEnvironment($this->getRequest()->getParam('environment'));
            $model->setConnectorKey($this->getRequest()->getParam('connector_key'));
            $model->setScopeHash($this->getRequest()->getParam('scopeHash'));
            $model->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

            switch ($model->getScope()) {
            case 'default':
                $model->setName($this->__('Default'));
                break;
            case 'website':
                $model->setName(Mage::app()
                    ->getWebsite($model->getScopeId())
                    ->getName());
                break;
            default:
                $model->setName(Mage::app()
                    ->getStore($model->getScopeId())
                    ->getName());
            }

            if (!$model->getId()) {
                $model->save();
            }

            if (!$this->_middleware->register($model)) {
                $model->delete();
                throw new RuntimeException(__('Failed to register ' . $model->getName()));
            } else {
                $model->setIsActive(true);
                $model->save();
            }

            $session->addSuccess($this->__('The registration has been saved.'));
            $session->setPageData(false);
        } catch (InvalidArgumentException $e) {
            $session->addError($e->getMessage());
            $session->setPageData($this->getRequest()->getPost());
            return $this->_redirect('*/*/*');
        } catch (Exception $e) {
            $session->addError($this->__('Failed to save registration.'));
            $this->_logger->critical($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * @see parent
     */
    protected function _isAllowed()
    {
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('admin/system/brontosoftware_connector');
    }
}
