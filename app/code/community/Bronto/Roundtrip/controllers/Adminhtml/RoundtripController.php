<?php

/**
 * @category Bronto
 * @package Roundtrip
 */
class Bronto_Roundtrip_Adminhtml_RoundtripController extends Mage_Adminhtml_Controller_Action
{
    //  {{{ runAction()

    /**
     * Run immediately
     *
     * @return void
     * @access public
     */
    public function runAction()
    {
        try {
            // Process Roundtrip
            $model  = Mage::getModel('bronto_roundtrip/roundtrip');

            $result = $model->processRoundtrip();
            
            if ($result) {
                $this->_getSession()->addSuccess('Roundtrip Verification Passed');
            } else {
                $this->_getSession()->addError('Roundtrip Verification Failed');
            }

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::helper('bronto_roundtrip')->writeError($e);
        }

        $this->_redirect('*/system_config/edit', array('section' => 'bronto_roundtrip'));
    }
    
    /**
     * Briefly validates token via ajax.
     * 
     * @return json
     * @access public
     */
    public function AjaxvalidationAction()
    {
        $helper  = Mage::helper('bronto_roundtrip/data');
        $result = 'Needs Verification';
        
        // Get Params
        $token   = $this->getRequest()->getPost('token', false);
        $scope   = $this->getRequest()->getPost('scope', 'default');
        $scopeId = $this->getRequest()->getPost('scopeid', 0);
        
        try {
            // Catch Token if sent
            if ($token) {                
                if (Mage::helper('bronto_common')->validApiToken($token) === false) {
                    $result = 'Failed Verification';
                } else {
                    // Save if valid
                    Mage::getConfig()->saveConfig('bronto/settings/api_token', $token, $scope, $scopeId);
                    Mage::getConfig()->reinit();
                    Mage::app()->reinitStores();
                    
                    $helper->setRoundtripStatus($helper->getPath('status'), '1', $scope, $scopeId);
                    $result = 'Passed Verification';
                }
            } else {
                $result = 'Needs Verification';
            }
        } catch (Exception $e) {
            Mage::helper('bronto_roundtrip')->writeError($e);
            $result = 'Needs Verification';
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    //  }}}
    //  {{{ _isAllowed()

    /**
     * @return bool
     * @access protected
     */
    protected function _isAllowed()
    {
        return $this->_isSectionAllowed('bronto_roundtrip');
    }

    //  }}}
    //  {{{ _isSectionAllowed()

    /**
     * Check if specified section allowed in ACL
     *
     * Will forward to deniedAction(), if not allowed.
     *
     * @param string $section
     *
     * @return bool
     * @access protected
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

    //  }}}
}
