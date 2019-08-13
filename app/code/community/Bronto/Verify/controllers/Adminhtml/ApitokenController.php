<?php

/**
 * API Token Validation Controller
 *
 * @category  Bronto
 * @package   Bronto_Verify
 * @author    Adam Daniels <adam.daniels@atlanticbt.com>
 * @copyright 2013 Adam Daniels
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   0.1.0
 */
class Bronto_Verify_Adminhtml_ApitokenController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Briefly validates token via ajax.
     * @return json
     * @access public
     */
    public function AjaxvalidationAction()
    {
        $helper = Mage::helper('bronto_verify/apitoken');
        $result = 'Needs Verification';

        // Get Params
        $token = $this->getRequest()->getPost('token', false);
        $tokenStatus = '2';

        try {
            // Catch Token if sent
            if ($token) {
                // Verify Token
                if ($helper->validApiToken($token) === false) {
                    $result = 'Failed Verification';
                    $tokenStatus = '0';
                } else {
                    $result = 'Passed Verification';
                    $tokenStatus = '1';
                }
            } else {
                $result = 'Needs Verification';
            }
        } catch (Exception $e) {
            Mage::helper('bronto_verify/apitoken')->writeError($e);
            $result = 'Needs Verification';
        }

        $helper->setStatus($helper->getPath('token_status'), $tokenStatus);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * @return bool
     * @access protected
     */
    protected function _isAllowed()
    {
        return $this->_isSectionAllowed('bronto_verify');
    }

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
}
