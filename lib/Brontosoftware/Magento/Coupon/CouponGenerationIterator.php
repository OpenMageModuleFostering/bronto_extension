<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Coupon/CouponGenerationIterator.php
 */

class Brontosoftware_Magento_Coupon_CouponGenerationIterator implements Iterator
{
    protected $_rules;
    protected $_middleware;
    protected $_generators;
    protected $_currentCoupons;
    protected $_startTime;
    protected $_endtime;
    protected $_codePrefix;
    protected $_codeSuffix;
    protected $_limit;
    protected $_offset;

    /**
     * @param Brontosoftware_Magento_Connector_MiddlewareInterface $middleware
     * @param Brontosoftware_Magento_Core_Sales_RuleManagerInterface $rules
     * @param array $generators
     * @param mixed $startTime
     * @param mixed $endTime
     * @param mixed $codePrefix
     * @param mixed $codeSuffix
     */
    public function __construct(
        Brontosoftware_Magento_Connector_MiddlewareInterface $middleware,
        Brontosoftware_Magento_Core_Sales_RuleManagerInterface $rules,
        array $generators,
        $startTime = null,
        $endTime = null,
        $codePrefix = null,
        $codeSuffix = null,
        $limit = 20,
        $offset = 0
    ) {
        $this->_middleware = $middleware;
        $this->_rules = $rules;
        $this->_startTime = $startTime;
        $this->_endtime = $endTime;
        $this->_codePrefix = $codePrefix;
        $this->_codeSuffix = $codeSuffix;
        $this->_limit = $limit;
        $this->_offset = $offset;
        $this->_generators = new ArrayIterator($generators);
    }

    /**
     * @see parent
     */
    public function current()
    {
        $generator = $this->_generators->current();
        $storeId = $this->_middleware->defaultStoreId($generator['scope'], $generator['scopeId']);
        return new Brontosoftware_DataObject([
            'storeId' => $storeId,
            'ruleId' => $generator['ruleId'],
            'campaignId' => $generator['campaignId'],
            'coupons' => array( $this->_currentCoupons->current()->getCode() )
        ]);
    }

    /**
     * @see parent
     */
    public function key()
    {
        return $this->_currentCoupons->key();
    }

    /**
     * @see parent
     */
    public function next()
    {
        $this->_currentCoupons->next();
    }

    /**
     * @see parent
     */
    public function rewind()
    {
        $this->_generators->rewind();
    }

    /**
     * Sets the current limit for the coupons
     *
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Sets the current offset for the coupons
     *
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * @see parent
     */
    public function valid()
    {
        if (is_null($this->_currentCoupons) && $this->_generators->valid()) {
            $this->_unusedCoupons();
        } else if (is_null($this->_currentCoupons) && !$this->_generators->valid()) {
            return false;
        } else if (!$this->_currentCoupons->valid() && $this->_generators->valid()) {
            $this->_generators->next();
            $this->_unusedCoupons();
        }
        return $this->_currentCoupons->valid();
    }

    /**
     * Fill current coupons with with current generator
     *
     * @return void
     */
    protected function _unusedCoupons()
    {
        $generator = $this->_generators->current();
        if (!is_null($generator)) {
            $this->_currentCoupons = $this->_rules->unusedCoupons(
                $generator['ruleId'],
                $this->_startTime,
                $this->_endtime,
                $this->_codePrefix,
                $this->_codeSuffix,
                $this->_limit,
                $this->_offset);
        }
    }
}
