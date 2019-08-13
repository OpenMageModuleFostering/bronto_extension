<?php

class Brontosoftware_Connector_RedirectController extends Mage_Core_Controller_Front_Action
{
    const SERVICE_PARAM = 'service';
    const REDIRECT_PARAM = 'redirect_path';
    const HEADER_REFERER = 'referer';

    /**
     * Forwards to any applicable service or the homepage
     */
    public function indexAction()
    {
        $serviceName = $this->getRequest()->getParam(self::SERVICE_PARAM);
        $redirectPath = $this->getRequest()->getParam(self::REDIRECT_PARAM);

        $redirector = new Brontosoftware_Magento_Connector_Redirect();
        $redirector->setPath($redirectPath);
        $allParams = $this->getRequest()->getParams();
        foreach ($allParams as $key => $value) {
            if ($key == self::REDIRECT_PARAM || $key == self::SERVICE_PARAM) {
                continue;
            }
            $redirector->setParam($key, $value);
        }

        if (!empty($serviceName)) {
            $areaName = "brontosoftware_connector_redirect_{$serviceName}";
            Mage::dispatchEvent($areaName, array(
                'redirect' => $redirector,
                'request' => $this->getRequest(),
                'messages' => Mage::getSingleton('core/session')
            ));
        }

        if ($redirector->getIsReferer()) {
            $referer = $this->getRequest()->getHeader(self::HEADER_REFERER);
            $this->getResponse()->setRedirect($referer);
        } else {
            $redirectPath = $redirector->getPath();
            if (empty($redirectPath)) {
                $redirectPath = '/';
            }
            if (preg_match('/^http/', $redirectPath)) {
                $query = $this->getRequest()->getServer('QUERY_STRING', '');
                if (!empty($query)) {
                    $redirectPath .= '?' . $query;
                }
                $this->getResponse()->setRedirect($redirectPath);
            } else {
                $this->_redirect($redirectPath, $redirector->getParams());
            }
        }
    }
}
