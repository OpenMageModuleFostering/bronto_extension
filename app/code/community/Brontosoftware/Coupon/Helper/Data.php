<?php

class Brontosoftware_Coupon_Helper_Data extends Mage_Core_Helper_Abstract implements Brontosoftware_Magento_Coupon_SettingsInterface
{
    protected static $_validErrorCodes = array(
        self::INVALID_CODE => 'translateCode',
        self::DEPLETED_CODE => 'translateCode',
        self::EXPIRED_CODE => 'translateCode',
        self::CONFLICT_CODE => 'translateConflict'
    );

    /**
     * @see parent
     */
    public function isForced()
    {
        return $this->_getRequest()->has(self::FORCE_PARAM);
    }

    /**
     * @see parent
     */
    public function isEnabled($scopeType = 'default', $scopeId = null)
    {
        $store = Mage::app()->getStore($scopeId);
        return $store->getConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @see parent
     */
    public function getParams($scopeType = 'default', $scopeId = null)
    {
        $store = Mage::app()->getStore($scopeId);
        return array(
            $store->getConfig(self::XML_PATH_COUPON_PARAM),
            $store->getConfig(self::XML_PATH_INVALID_PARAM)
        );
    }

    /**
     * @see parent
     */
    public function isDisplayMessage($scopeType = 'default', $scopeId = null)
    {
        $store = Mage::app()->getStore($scopeId);
        return (bool)$store->getConfig(sprintf(self::XML_PATH_MESSAGE, 'display'));
    }

    /**
     * @see parent
     */
    public function getLinkContent($scopeType = 'default', $scopeId = null)
    {
        return Mage::app()->getStore($scopeId)->getConfig(self::XML_PATH_LINK_CONTENT);
    }

    /**
     * @see parent
     */
    public function applyCodeFromRequest($messages, $store)
    {
        list($couponParam, $errorParam) = $this->getParams();
        $request     = $this->_getRequest();
        $errorCode   = $request->getParam($errorParam, null);
        $couponCode  = $request->getParam($couponParam, null);
        if ($errorCode || $couponCode) {
            if (!empty($couponCode)) {
                try {
                    $coupon = $this->_validateCode($couponCode, $this->isForced());
                    if (!$this->_isApplied($coupon->getRuleId(), $couponCode)) {
                        $this->applyCode($coupon->getRuleId(), $couponCode);
                        if ($this->isDisplayMessage('store', $store)) {
                            $messages->addSuccess($this->_successMessage($couponCode, $store));
                        }
                    }
                    return true;
                } catch (Exception $e) {
                    $errorCode = $e->getMessage();
                }
            }
            if (!$this->_isValidCode($errorCode)) {
                $errorCode = self::INVALID_CODE;
            }
            if ($this->isDisplayMessage('store', $store)) {
                $messages->addError($this->_errorMessage($errorCode, $couponCode, $store));
            }
        }
        return false;
    }

    /**
     * @see parent
     */
    public function applyCode($ruleId = null, $couponCode = null)
    {
        $session = Mage::getSingleton('core/session');
        if (is_null($couponCode)) {
            $couponCode = $session->getCouponCode();
            $ruleId = $session->getRuleId();
        } else {
            $session->setCouponCode($couponCode);
            $session->setRuleId($ruleId);
        }
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        if ($quote && $couponCode) {
            $quote->setCouponCode($couponCode)->save();
            if ($this->_isRuleApplied($ruleId)) {
                $session->unsCouponCode($couponCode);
                $session->unsRuleId($ruleId);
            }
        }
    }

    /**
     * Translates the code in the message
     *
     * @param string $message
     * @param string $couponCode
     * @param string $key
     * @return string
     */
    public function translateCode($message, $couponCode, $key = 'code')
    {
        return $this->__(str_replace('{' . $key . '}', $couponCode, $message));
    }

    /**
     * Translate the conflict message with conflict params
     *
     * @param string $message
     * @param string $couponCode
     * @return string
     */
    public function translateConflict($message, $couponCode)
    {
        list($couponParam, $invalidParam) = $this->getParams();
        $forceUrl = Mage::app()->getStore()->getUrl('*/*/*', array(
            $couponParam => $couponCode,
            self::FORCE_PARAM => 1,
        ));
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $linkContent = $this->getLinkContent();
        $replacements = array(
            'link' => '<a href="' . $forceUrl . '">' . $linkContent . '</a>',
            'oldCode' => $quote->getCouponCode(),
            'newCode' => $couponCode,
        );
        foreach ($replacements as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        return $this->__($message);
    }

    /**
     * Gets the configured error message
     *
     * @param string $errorCode
     * @param string $couponCode
     * @param mixed $storeId
     * @return string
     */
    protected function _errorMessage($errorCode, $couponCode, $storeId = null)
    {
        $store = Mage::app()->getStore($storeId);
        $message = $store->getConfig(sprintf(self::XML_PATH_MESSAGE, $errorCode));
        $translateCallback = self::$_validErrorCodes[$errorCode];
        return $this->$translateCallback($message, empty($couponCode) ? 'code' : $couponCode);
    }

    /**
     * Gets the configured success message
     *
     * @param string $couponCode
     * @param mixed $storeId
     * @return string
     */
    protected function _successMessage($couponCode, $storeId = null)
    {
        $store = Mage::app()->getStore($storeId);
        $message = $store->getConfig(sprintf(self::XML_PATH_MESSAGE, 'success'));
        return $this->translateCode($message, $couponCode);
    }

    /**
     * Tests if the message code is a valid one
     *
     * @param string $code
     * @return string
     */
    protected function _isValidCode($code)
    {
        return array_key_exists($code, self::$_validErrorCodes);
    }

    /**
     * Validates the coupon
     *
     * @param string $couponCode
     * @param boolean $force
     * @return Mage_SalesRule_Model_Coupon
     */
    protected function _validateCode($couponCode, $force = false)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $rules = Mage::getModel('salesrule/rule')
            ->getCollection()
            ->setValidationFilter($websiteId, $customerGroupId, $couponCode)
            ->addFieldToFilter('main_table.coupon_type', array('in' => array(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC, Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO)));
        foreach ($rules as $rule) {
            $coupon = Mage::getModel('salesrule/coupon')->loadByCode($couponCode);
            if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                Mage::throwException('depleted');
            }
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if ($quote) {
                if (!$force && $quote->getCouponCode() && $quote->getCouponCode() != $couponCode) {
                    Mage::throwException('conflict');
                }
            }
            return $coupon;
        }
        Mage::throwException('invalid');
    }

    /**
     * Checks if the rule has been applied to the cart
     *
     * @param mixed $ruleId
     * @return boolean
     */
    protected function _isRuleApplied($ruleId)
    {
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        if ($quote) {
            return in_array($ruleId, explode(',', $quote->getAppliedRuleIds()));
        }
        return false;
    }

    /**
     * Determines if the code has been applied to the session
     *
     * @param mixed $ruleId
     * @param string $couponCode
     * @return boolean
     */
    protected function _isApplied($ruleId, $couponCode)
    {
        if (!$this->_isRuleApplied($ruleId)) {
            return false;
        }
        $session = Mage::getSingleton('core/session');
        return $session->getCouponCode() == $couponCode;
    }
}
