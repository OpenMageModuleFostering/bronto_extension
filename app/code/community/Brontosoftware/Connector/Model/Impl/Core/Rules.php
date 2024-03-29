<?php

class Brontosoftware_Connector_Model_Impl_Core_Rules implements Brontosoftware_Magento_Core_Sales_RuleManagerInterface
{
    const MAX_ATTEMPTS = 10;

    protected $_ruleCache = array();

    /**
     * @see parent
     */
    public function getBySource(Brontosoftware_Magento_Connector_Discovery_Source $source, $onlyPools = true)
    {
        $collection = Mage::getModel('salesrule/rule')->getCollection();
        if ($onlyPools) {
            $collection->addFieldToFilter(array('coupon_type', 'use_auto_generation'), array(
                array( 'eq' => '3' ),
                array( 'eq' => '1' )
            ));
        } else {
            $collection
                ->addFieldToFilter('coupon_type', array( 'eq' => '2' ))
                ->addFieldToFilter('use_auto_generation', array( 'eq' => '0' ));
        }
        list($scopeName, $scopeId) = explode('.', $source->getScopeHash());
        switch ($scopeName) {
            case 'store':
                $store = Mage::app()->getStore($scopeId);
                $scopeId = $store->getWebsiteId();
            case 'website':
                $collection->addWebsiteFilter($scopeId);
        }
        foreach ($source->getFilters() as $field => $value) {
            $collection->addFieldToFilter($field, array( 'like' => "%$value" ));
        }
        if ($source->getId()) {
            $collection->addFieldToFilter('rule_id', array( 'eq' => $source->getId() ));
        }
        $collection->getSelect()->limitPage($source->getOffset(), $source->getLimit());
        return $collection;
    }

    /**
     * @see parent
     */
    public function getById($ruleId)
    {
        if (!array_key_exists($ruleId, $this->_ruleCache)) {
            $rule = Mage::getModel('salesrule/rule')->load($ruleId);
            if ($rule->getId()) {
                $this->_ruleCache[$rule->getId()] = $rule;
            } else {
                $this->_ruleCache[$rule->getId()] = null;
            }
        }
        return $this->_ruleCache[$ruleId];
    }

    /**
     * @see parent
     */
    public function isReplenishable($data)
    {
        $coupons = $this->unusedCoupons($data['ruleId'])->getSize();
        return $coupons == 0;
    }

    /**
     * @see parent
     */
    public function acquireCoupons($data)
    {
        $generator = Mage::getModel('salesrule/coupon_massgenerator');
        $generator->setData($data);
        $codes = array();
        try {
            // Note: This was largely modified from the generator class
            $coupon = Mage::getModel('salesrule/coupon');
            $now = $generator->getResource()->formatDate(Mage::getSingleton('core/date')->gmtTimestamp());
            for ($i = 0; $i < $generator->getQty(); $i++) {
                $attempt = 0;
                do {
                    if ($attempt >= self::MAX_ATTEMPTS) {
                        Mage::throwException(Mage::helper('brontosoftware_coupon')->__('Unable to create requested Coupon Qty.'));
                    }
                    $code = $generator->generateCode();
                    $attempt++;
                } while ($generator->getResource()->exists($code));

                $expirationDate = $generator->getToDate();
                if ($expirationDate instanceof Zend_Date) {
                    $expirationDate = $expirationDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                }

                $coupon->setId(null)
                    ->setRuleId($generator->getRuleId())
                    ->setUsageLimit($generator->getUsesPerCoupon())
                    ->setUsagePerCustomer($generator->getUsesPerCustomer())
                    ->setExpirationDate($expirationDate)
                    ->setCreatedAt($now)
                    ->setType(Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                    ->setCode($code)
                    ->save();
                $codes[] = $code;
            }
        } catch (Exception $e) {
            Mage::getSingleton('brontosoftware_connector/impl_core_logger')->critical($e);
        }
        return $codes;
    }

    /**
     * @see parent
     */
    public function unusedCoupons($ruleId, $startTime = null, $endTime = null, $codePrefix = null, $codeSuffix = null, $limit = 20, $offset = 0)
    {
        $coupons = Mage::getModel('salesrule/coupon')
            ->getCollection()
            ->addRuleToFilter($ruleId);
        if (!is_null($startTime)) {
            $coupons->addFieldToFilter('created_at', array( 'gt' => $startTime ));
        }
        if (!is_null($endTime)) {
            $coupons->addFieldToFilter('created_at', array( 'lt' => $endTime ));
        }
        if (!is_null($codePrefix)) {
            $coupons->addFieldToFilter('code', array( 'like' => "{$codePrefix}%"  ));
        }
        if (!is_null($codeSuffix)) {
            $coupons->addFieldToFilter('code', array( 'like' => "%{$codeSuffix}"  ));
        }
        $coupons->addFieldToFilter('times_used', array( 'eq' => 0 ));
        $coupons->getSelect()->limitPage($offset, $limit);
        return $coupons;
    }
}
