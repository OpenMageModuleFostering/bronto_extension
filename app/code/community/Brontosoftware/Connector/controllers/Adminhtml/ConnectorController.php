<?php

class Brontosoftware_Connector_Adminhtml_ConnectorController extends Mage_Adminhtml_Controller_Action
{
    const X_AUTHORIZATION = 'X-Authorization';
    const AUTHORIZATION = 'Authorization';
    const UNAUTHORIZED = 401;
    const NOT_FOUND = 404;
    const GONE = 410;

    private $_encoder;
    private $_middleware;
    private $_logger;

    /**
     * @see parent
     */
    protected function _construct()
    {
        $this->_encoder = new Brontosoftware_Serialize_Json_Standard();
        $this->_logger = Mage::getModel('brontosoftware_connector/impl_core_logger');
        $this->_middleware = Mage::getModel('brontosoftware_connector/impl_connector_middleware');
    }

    /**
     * Sends a JSON containing the discovery document
     */
    public function discoveryAction()
    {
        return $this->_execute('discovery');
    }

    /**
     * Sends a JSON containing the endpoint document
     */
    public function endpointAction()
    {
        return $this->_execute('endpoint');
    }

    /**
     * Sends a JSON object containing the scope tree
     */
    public function scopeAction()
    {
        return $this->_execute('scope');
    }

    /**
     * Handles job triggers from the Middleware
     */
    public function scriptAction()
    {
        return $this->_execute('script');
    }

    /**
     * Handles settings sync triggers
     */
    public function settingsAction()
    {
        return $this->_execute('settings');
    }

    /**
     * Handles source pulls
     */
    public function sourceAction()
    {
        return $this->_execute('source');
    }

    /**
     * Handles trigger pulls
     */
    public function triggerAction()
    {
        return $this->_execute('trigger');
    }

    /**
     * Execution wrapper for all of the endpoints
     *
     * @param string $area
     * @return void
     */
    protected function _execute($area)
    {
        if ($registration = $this->_registration()) {
            $methodName = "_{$area}";
            if (method_exists($this, $methodName)) {
                try {
                    $json = call_user_func(array($this, $methodName), $registration);
                } catch (Exception $e) {
                    $this->_logger->critical($e);
                    $json['code'] = 500;
                    $json['message'] = $e->getMessage();
                }
            }
            $this->getResponse()
                ->setHeader('Content-Type', $this->_encoder->getMimeType())
                ->setHttpResponseCode(array_key_exists('code', $json) ? $json['code'] : 200)
                ->setBody($this->_encoder->encode($json));
        }
    }

    /**
     * @return array
     */
    protected function _discovery($registration)
    {
        return $this->_middleware->discovery($registration);
    }

    /**
     * @return array
     */
    protected function _endpoint($registration)
    {
        $service = $this->getRequest()->getParam('service');
        if ($service) {
            $endpoint = $this->_middleware->endpoint($registration, $service);
            if (empty($endpoint)) {
                return array('message' => "{$service} not found.", 'code' => 404);
            } else {
                return $endpoint;
            }
        } else {
            return array('message' => 'No service provided.', 'code' => 400);
        }
    }

    /**
     * @return array
     */
    protected function _source($registration)
    {
        $sourceId = $this->getRequest()->getParam('object');
        if ($sourceId) {
            $content = $this->getRequest()->getRawBody();
            $data = array();
            if (!empty($content)) {
                $data = $this->_encoder->decode($content);
            }
            return $this->_middleware->source($registration, $sourceId, $data);
        } else {
            return array(
                'message' => $this->__('No source provided.'),
                'code' => 400
            );
        }
    }

    /**
     * @return array
     */
    protected function _scope($registration)
    {
        return $this->_middleware->scopeTree($registration);
    }

    /**
     * @return array
     */
    protected function _script($registration)
    {
        $extensionId = $this->getRequest()->getParam('extensionId');
        $scriptId = $this->getRequest()->getParam('scriptId');
        if (empty($extensionId) || empty($scriptId)) {
            return array(
                'message' => $this->__('Required: extensionId and scriptId'),
                'code' => 400
            );
        } else {
            $content = $this->getRequest()->getRawBody();
            $data = array();
            if (!empty($content)) {
                $data = $this->_encoder->decode($content);
            }
            return $this->_middleware->executeScript($registration, array(
                'extensionId' => $extensionId,
                'id' => $scriptId,
                'data' => $data
            ));
        }
    }

    /**
     * @return array
     */
    protected function _settings($registration)
    {
        $success = true;
        if ($this->getRequest()->getParam('changed', 'true') == 'true') {
            $success = $success && $this->_middleware->sync($registration);
        }
        if ($this->getRequest()->getParam('trigger', 'true') == 'true') {
            $success = $success && $this->_middleware->triggerFlush($registration);
        }
        if ($success) {
            return array('message' => 'success');
        } else {
            return array('message' => 'failed', 'code' => 400);
        }
    }

    /**
     * @return array
     */
    protected function _trigger($registration)
    {
        if ($this->_middleware->triggerFlush($registration)) {
            return array( 'message' => 'success' );
        } else {
            return array( 'message' => 'failed', 'code' => 400 );
        }
    }

    /**
     * Scans the auth token off of the authorization header
     *
     * @return string
     */
    protected function _authToken()
    {
        $authToken = $this->getRequest()->getHeader(self::X_AUTHORIZATION);
        if (empty($authToken)) {
            $authToken = $this->getRequest()->getHeader(self::AUTHORIZATION);
        }
        if (empty($authToken)) {
            $this->getResponse()->setHttpResponseCode(self::UNAUTHORIZED);
            return null;
        }
        return $authToken;
    }

    /**
     * Uses the authorization header to find a related registration
     *
     * @return mixed
     */
    protected function _registration()
    {
        if ($authToken = $this->_authToken()) {
            $crypt = Mage::getModel('core/encryption');
            list($scope, $scopeId) = explode('.', $crypt->decrypt(rawurldecode($authToken)));
            $registration = Mage::getModel('brontosoftware_connector/registration')
                ->loadByScope($scope, $scopeId);
            if (!$registration->hasEntityId()) {
                $this->getResponse()->setHttpResponseCode(self::GONE);
                return null;
            }
            return $registration;
        }
        return null;
    }

    /**
     * @see parent
     */
    public function preDispatch()
    {
        return Mage_Core_Controller_Varien_Action::preDispatch();
    }

    /**
     * @see parent
     */
    protected function _isAllowed()
    {
        return true;
    }
}
