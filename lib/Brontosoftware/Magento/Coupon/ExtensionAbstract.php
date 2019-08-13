<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Coupon/ExtensionAbstract.php
 */

abstract class Brontosoftware_Magento_Coupon_ExtensionAbstract extends Brontosoftware_Magento_Connector_Discovery_AdvancedExtensionAbstract implements Brontosoftware_Magento_Connector_Discovery_GroupInterface, Brontosoftware_Magento_Email_FilterEventInterface
{
    protected $_rules;
    protected $_manager;
    protected $_middleware;

    /**
     * @param Brontosoftware_Magento_Core_Sales_RuleManagerInterface $rules
     * @param Brontosoftware_Magento_Coupon_ManagerInterface $manager
     * @param Brontosoftware_Magento_Connector_MiddlewareInterface $middleware
     * @param Brontosoftware_Magento_Core_App_EmulationInterface $appEmulation
     * @param Brontosoftware_Magento_Core_Store_ManagerInterface $storeManager
     * @param Brontosoftware_Magento_Connector_QueueManagerInterface $queueManager
     * @param Brontosoftware_Magento_Connector_SettingsInterface $connectorSettings
     * @param Brontosoftware_Magento_Coupon_SettingsInterface $helper
     * @param Brontosoftware_Magento_Connector_Event_PlatformInterface $platform
     * @param Brontosoftware_Magento_Connector_Event_SourceInterface $source
     */
    public function __construct(
        Brontosoftware_Magento_Core_Sales_RuleManagerInterface $rules,
        Brontosoftware_Magento_Coupon_ManagerInterface $manager,
        Brontosoftware_Magento_Connector_MiddlewareInterface $middleware,
        Brontosoftware_Magento_Core_App_EmulationInterface $appEmulation,
        Brontosoftware_Magento_Core_Store_ManagerInterface $storeManager,
        Brontosoftware_Magento_Connector_QueueManagerInterface $queueManager,
        Brontosoftware_Magento_Connector_SettingsInterface $connectorSettings,
        Brontosoftware_Magento_Coupon_SettingsInterface $helper,
        Brontosoftware_Magento_Connector_Event_PlatformInterface $platform,
        Brontosoftware_Magento_Connector_Event_SourceInterface $source
    ) {
        parent::__construct(
            $appEmulation,
            $storeManager,
            $queueManager,
            $connectorSettings,
            $helper,
            $platform,
            $source);
        $this->_middleware = $middleware;
        $this->_rules = $rules;
        $this->_manager = $manager;
    }

    /**
     * @see parent
     */
    public function getSortOrder()
    {
        return 80;
    }

    /**
     * @see parent
     */
    public function getEndpointName()
    {
        return $this->translate('Coupons');
    }

    /**
     * @see parent
     */
    public function getEndpointId()
    {
        return 'coupon';
    }

    /**
     * @see parent
     */
    public function getEndpointIcon()
    {
        return 'mage-icon-coupons';
    }

    /**
     * @see parent
     */
    public function gatherEndpoints($observer)
    {
        $observer->getDiscovery()->addGroupHelper($this);
    }

    /**
     * Gets coupon pools from the platform
     *
     * @param mixed $observer
     * @return void
     */
    public function pullCouponPools($observer)
    {
        $this->_pullCoupons($observer->getSource(), true);
    }

    /**
     * Gets coupon codes from the platform
     *
     * @param mixed $observer
     * @return void
     */
    public function pullCouponSpecific($observer)
    {
        $this->_pullCoupons($observer->getSource(), false);
    }

    /**
     * Trigger Middleware to replenish pools
     *
     * @param mixed $observer
     * @return void
     */
    public function triggerReplenish($observer)
    {
        $script = $observer->getScript();
        $poolIds = $this->_manager->getReplenishablePoolIds($script->getRegistration());
        if (!empty($poolIds)) {
            $script->addScheduledTask('triggerReplenish', array(
                'importSelected' => implode(',', $poolIds),
                'autoTriggered' => true,
                'replenishTimes' => 1
            ));
        }
    }

    /**
     * Replenishes coupons using associated generator
     *
     * @param mixed $observer
     * @return void
     */
    public function replenishCoupons($observer)
    {
        $results = array(
            'success' => 0,
            'error' => 0,
            'skipped' => 0,
            'couponManagerUploaded' => 0
        );
        $script = $observer->getScript()->getObject();
        $data = $script['data'];
        $registration = $observer->getScript()->getRegistration();
        $autoTriggered = array_key_exists('autoTriggered', $data) ? $data['autoTriggered'] : false;
        $times = array_key_exists('replenishTimes', $data) ? $data['replenishTimes'] : 1;
        if ($data['requestId'] < $times) {
            $storeId = $this->_middleware->defaultStoreId($registration->getScope(), $registration->getScopeId());
            if (!$autoTriggered || $this->_helper->isEnabled('store', $storeId)) {
                foreach ($this->_registeredGenerators($registration, $data) as $generator) {
                    $coupons = $this->_manager->acquireCoupons($generator);
                    $updatedAmount = count($coupons);
                    if ($generator['integration']) {
                        $coupons = new Brontosoftware_DataObject([
                            'campaignId' => $generator['campaignId'],
                            'ruleId' => $generator['ruleId'],
                            'coupons' => $coupons
                        ]);
                        $action = $this->_source->action($coupons);
                        if (!empty($action)) {
                            $event = $this->_platform->annotate($this->_source, $coupons, $action, $storeId);
                            if ($this->_platform->dispatch($event) || $this->_queueManager->save($event)) {
                                $results['couponManagerUploaded'] += $updatedAmount;
                            }
                        }
                    }
                    $results['success'] += $updatedAmount;
                }
            }
        }
        $observer->getScript()->setProgress($results);
    }

    /**
     * Observe the Bronto redirect to apply coupons
     *
     * @param mixed $observer
     * @return void
     */
    public function applyCoupon($observer)
    {
        $store = $this->_storeManager->getStore(true);
        if ($this->_helper->isEnabled('store', $store)) {
            $this->_helper->applyCodeFromRequest($observer->getMessages(), $store);
        }
        if ($this->_helper->isForced()) {
            $observer->getRedirect()->setIsReferer(true);
        } else {
            $allParams = $observer->getRedirect()->getParams();
            foreach ($this->_helper->getParams('store', $store) as $stripped) {
                if (array_key_exists($stripped, $allParams)) {
                    unset($allParams[$stripped]);
                }
            }
            $observer->getRedirect()->setParams($allParams);
        }
    }

    /**
     * @see parent
     */
    public function advancedAdditional($observer)
    {
        $observer->getEndpoint()->addOptionToScript("historical", "jobName", array(
            'id' => $this->getEndpointId(),
            'name' => $this->translate('Coupon')
        ));

        $observer->getEndpoint()->addFieldToScript('historical', array(
            'id' => 'importSelected',
            'name' => $this->translate('Select Generator'),
            'type' => 'select',
            'typeProperties' => array(
                'objectType' => array(
                    'extension' => 'coupon',
                    'id' => 'generator',
                )
            ),
            'depends' => array(
                array(
                    'id' => 'jobName',
                    'values' => array( $this->getEndpointId() )
                )
            )
        ));

        $observer->getEndpoint()->addFieldToScript('historical', array(
            'id' => 'codePrefix',
            'name' => $this->translate('Code Prefix Filter'),
            'type' => 'text',
            'depends' => array(
                array(
                    'id' => 'jobName',
                    'values' => array( $this->getEndpointId() )
                )
            )
        ));

        $observer->getEndpoint()->addFieldToScript('historical', array(
            'id' => 'codeSuffix',
            'name' => $this->translate('Code Suffix Filter'),
            'type' => 'text',
            'depends' => array(
                array(
                    'id' => 'jobName',
                    'values' => array( $this->getEndpointId() )
                )
            )
        ));

        $observer->getEndpoint()->addOptionToScript('event', 'jobName', array(
            'id' => 'triggerReplenish',
            'name' => $this->translate('Replenish Coupon Pool'),
        ));

        $observer->getEndpoint()->addOptionToScript('event', 'moduleSettings', array(
            'id' => $this->getEndpointId(),
            'name' => $this->getEndpointName()
        ));

        $observer->getEndpoint()->addFieldToScript('event', array(
            'id' => 'importSelected',
            'name' => $this->translate('Select Generator'),
            'type' => 'select',
            'typeProperties' => array(
                'objectType' => array(
                    'extension' => 'coupon',
                    'id' => 'generator',
                )
            ),
            'depends' => array(
                array(
                    'id' => 'jobName',
                    'values' => array( 'triggerReplenish' )
                )
            )
        ));
    }

    /**
     * Observe the Bronto redirect to apply coupons
     *
     * @param mixed $observer
     * @return void
     */
    public function applyCodeOnCartAfterItem($observer)
    {
        $product = $observer->getProduct();
        if ($this->_helper->isEnabled('store', $product->getStoreId())) {
            $this->_helper->applyCode();
        }
    }

    /**
     * @see parent
     */
    public function endpointInfo($observer)
    {
        $couponFormats = $this->_couponFormats();
        $observer->getEndpoint()->addSource(array(
            'id' => 'coupon_pool',
            'name' => $this->translate('Rule Pool'),
            'filters' => array(
                array(
                    'id' => 'name',
                    'name' => $this->translate('Name'),
                    'type' => 'text'
                )
            ),
            'fields' => array(
                array(
                    'id' => 'id',
                    'name' => $this->translate('ID'),
                    'width' => '2'
                ),
                array(
                    'id' => 'name',
                    'name' => $this->translate('Name'),
                    'width' => '4'
                ),
                array(
                    'id' => 'type',
                    'name' => $this->translate('Coupon Type'),
                    'width' => '4'
                ),
                array(
                    'id' => 'active',
                    'name' => $this->translate('Active'),
                    'width' => '2'
                )
            )
        ));

        $observer->getEndpoint()->addObject(array(
            'id' => 'generator',
            'name' => $this->translate('Coupon Generator'),
            'shortName' => $this->translate('Coupons'),
            'identifiable' => true,
            'fields' => array(
                array(
                    'id' => 'name',
                    'name' => $this->translate('Name'),
                    'type' => 'text',
                    'required' => true,
                    'position' => 1,
                ),
                array(
                    'id' => 'enabled',
                    'name' => $this->translate('Enabled'),
                    'type' => 'boolean',
                    'required' => true,
                    'position' => 2,
                    'typeProperties' => array(
                        'default' => false
                    )
                ),
                array(
                    'id' => 'ruleId',
                    'name' => $this->translate('Shopping Cart Price Rule'),
                    'type' => 'source',
                    'required' => true,
                    'position' => 3,
                    'typeProperties' => array(
                        'source' => 'coupon_pool'
                    )
                ),
                array(
                    'id' => 'format',
                    'name' => $this->translate('Code Format'),
                    'type' => 'select',
                    'required' => true,
                    'position' => 6,
                    'typeProperties' => array(
                        'default' => $couponFormats[0]['id'],
                        'options' => $couponFormats
                    )
                ),
                array(
                    'id' => 'length',
                    'name' => $this->translate('Code Length'),
                    'type' => 'integer',
                    'required' => true,
                    'position' => 7,
                    'typeProperties' => array(
                        'default' => 12,
                        'min' => 1,
                        'max' => 32
                    )
                ),
                array(
                    'id' => 'prefix',
                    'name' => $this->translate('Code Prefix'),
                    'type' => 'text',
                    'position' => 8,
                ),
                array(
                    'id' => 'suffix',
                    'name' => $this->translate('Code Suffix'),
                    'type' => 'text',
                    'position' => 9,
                ),
                array(
                    'id' => 'dashInterval',
                    'name' => $this->translate('Dash Every X Characters'),
                    'type' => 'integer',
                    'position' => 10,
                    'typeProperties' => array(
                        'default' => 0,
                        'min' => 0,
                        'max' => 31
                    )
                ),
                array(
                    'id' => 'integration',
                    'name' => $this->translate('Coupon Campaign Sync'),
                    'type' => 'boolean',
                    'typeProperties' => array(
                        'default' => false
                    )
                ),
                array(
                    'id' => 'campaignId',
                    'name' => $this->translate('Coupon Campaign'),
                    'type' => 'select',
                    'required' => true,
                    'typeProperties' => array(
                        'bronto' => array( 'type' => 'couponManager' )
                    ),
                    'depends' => array(
                        array( 'id' => 'integration', 'values' => array(true))
                    )
                ),
                array(
                    'id' => 'threshold',
                    'name' => $this->translate('Replenish Threshold'),
                    'type' => 'integer',
                    'typeProperties' => array(
                        'default' => 1000
                    ),
                    'required' => true,
                    'depends' => array(
                        array( 'id' => 'integration', 'values' => array(true))
                    )
                ),
                array(
                    'id' => 'amount',
                    'name' => $this->translate('Replenish Amount'),
                    'type' => 'integer',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => 100,
                        'min' => 1,
                        'max' => 1000
                    ),
                    'depends' => array(
                        array( 'id' => 'integration', 'values' => array(true))
                    )
                ),
                array(
                    'id' => 'endDate',
                    'name' => $this->translate('Replenish Until'),
                    'type' => 'date',
                    'depends' => array(
                        array( 'id' => 'integration', 'values' => array(true))
                    )
                ),
            )
        ));

        $observer->getEndpoint()->addExtension(array(
            'id' => 'settings',
            'name' => $this->translate('Settings'),
            'fields' => array(
                array(
                    'id' => 'enabled',
                    'name' => $this->translate('Enabled'),
                    'type' => 'boolean',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => false,
                    )
                ),
                array(
                    'id' => 'coupon_param',
                    'name' => $this->translate('Coupon Code Query Parameter'),
                    'type' => 'text',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => 'coupon'
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        )
                    )
                ),
                array(
                    'id' => 'invalid_param',
                    'name' => $this->translate('Invalid Coupon Query Parameter'),
                    'type' => 'text',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => 'invalid_coupon'
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        )
                    )
                ),
                array(
                    'id' => 'display_message',
                    'name' => $this->translate('Display Message'),
                    'type' => 'boolean',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => false
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        )
                    )
                ),
                array(
                    'id' => 'success_message',
                    'name' => $this->translate('Success Message'),
                    'type' => 'textarea',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => $this->translate('Coupon {code} was successfully applied to your shopping session.')
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        ),
                        array(
                            'id' => 'display_message',
                            'values' => array(true)
                        )
                    )
                ),
                array(
                    'id' => 'invalid_message',
                    'name' => $this->translate('Invalid Message'),
                    'type' => 'textarea',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => $this->translate('Coupon {code} is invalid.')
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        ),
                        array(
                            'id' => 'display_message',
                            'values' => array(true)
                        )
                    )
                ),
                array(
                    'id' => 'depleted_message',
                    'name' => $this->translate('Depleted Message'),
                    'type' => 'textarea',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => $this->translate('Coupon {code} has been depleted.')
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        ),
                        array(
                            'id' => 'display_message',
                            'values' => array(true)
                        )
                    )
                ),
                array(
                    'id' => 'expired_message',
                    'name' => $this->translate('Expired Message'),
                    'type' => 'textarea',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => $this->translate('Coupon {code} has expired.')
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        ),
                        array(
                            'id' => 'display_message',
                            'values' => array(true)
                        )
                    )
                ),
                array(
                    'id' => 'conflict_message',
                    'name' => $this->translate('Conflict Message'),
                    'type' => 'textarea',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => $this->translate('Your shopping session already has coupon {oldCode} applied. {link} to apply {newCode} instead.'),
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        ),
                        array(
                            'id' => 'display_message',
                            'values' => array(true)
                        )
                    )
                ),
                array(
                    'id' => 'link_text',
                    'name' => $this->translate('Link Text'),
                    'type' => 'text',
                    'required' => true,
                    'typeProperties' => array(
                        'default' => $this->translate('Click here'),
                    ),
                    'depends' => array(
                        array(
                            'id' => 'enabled',
                            'values' => array(true)
                        ),
                        array(
                            'id' => 'display_message',
                            'values' => array(true)
                        )
                    )
                ),
            )
        ));
    }

    /**
     * Adds coupons to the message editor
     *
     * @param mixed $observer
     */
    public function messageExtras($observer)
    {
        $options = $observer->getContainer()->getOptions();
        $fields = $observer->getContainer()->getFields();
        $couponType = array(
            'id' => 'couponType',
            'name' => $this->translate('Coupon Type'),
            'type' => 'select',
            'advanced' => isset($options['advanced']),
            'typeProperties' => array(
                'options' => array(
                    array( 'id' => 'none', 'name' => $this->translate('No Coupon') ),
                    array( 'id' => 'specific', 'name' => $this->translate('Specific Coupon') ),
                    array( 'id' => 'generator', 'name' => $this->translate('Generator') )
                )
            )
        );
        if (!isset($options['advanced'])) {
            $couponType['typeProperties']['default'] = 'none';
        }
        $fields[] = $couponType;
        $fields[] = array(
            'id' => 'ruleId',
            'name' => $this->translate('Specific Coupon'),
            'type' => 'source',
            'advanced' => isset($options['advanced']),
            'depends' => array(
                array( 'id' => 'couponType', 'values' => array('specific') )
            ),
            'typeProperties' => array(
                'source' => 'coupon_code'
            )
        );
        $fields[] = array(
            'id' => 'generatorId',
            'name' => $this->translate('Coupon Generator'),
            'type' => 'object',
            'advanced' => isset($options['advanced']),
            'depends' => array(
                array( 'id' => 'couponType', 'values' => array('generator') )
            ),
            'typeProperties' => array(
                'objectType' => array(
                    'extension' => 'coupon',
                    'id' => 'generator'
                )
            )
        );
        $observer->getContainer()->setFields($fields);
    }

    /**
     * Adds email template filter to add couponCode tag
     *
     * @param mixed $observer
     * @return void
     */
    public function eventFilter($observer)
    {
        $observer->getFilter()->addEventFilter($this);
    }

    /**
     * @see parent
     */
    public function apply($message, $templateVars = array(), $forceContext)
    {
        $ret = array();
        if (array_key_exists('couponType', $message)) {
            $ret = array( 'coupon' => $message['couponType'] );
            if (!$forceContext) {
                $couponCode = '';
                switch ($message['couponType']) {
                case 'generator':
                    $couponCode = $this->_manager->acquireCoupon($message['generatorId']);
                    break;
                case 'specific':
                    $rule = $this->_rules->getById($message['ruleId']);
                    if ($rule) {
                        $coupon = $rule->getPrimaryCoupon();
                        if ($coupon) {
                            $couponCode = $coupon->getCode();
                        }
                    }
                }
                $ret = array('couponCode' => $couponCode);
            }
        }
        return $ret;
    }

    /**
     * Implementors fill this in
     *
     * @return array
     */
    abstract protected function _couponFormats();

    /**
     * @see parent
     */
    protected function _sendHistorical($registration, $data)
    {
        $objects = array();
        $fromDate = null;
        if (array_key_exists('startTime', $data)) {
            $startTime = $data['startTime'];
            if ($startTime) {
                $fromDate = strtotime($startTime);
            }
        }
        $toDate = null;
        if (array_key_exists('endTime', $data)) {
            $endTime = $data['endTime'];
            if ($endTime) {
                $toDate = strtotime($endTime);
            }
        }
        if (array_key_exists('options', $data)) {
            $codePrefix = null;
            if (!empty($data['options']['codePrefix'])) {
                $codePrefix = $data['codePrefix'];
            }
            $codeSuffix = null;
            if (!empty($data['options']['codeSuffix'])) {
                $codeSuffix = $data['codeSuffix'];
            }
        }
        return new Brontosoftware_Magento_Coupon_CouponGenerationIterator(
            $this->_middleware,
            $this->_rules,
            $this->_registeredGenerators($registration, $data['options']),
            $fromDate,
            $toDate,
            $codePrefix,
            $codeSuffix);
    }

    /**
     * @see parent
     */
    protected function _applyLimitOffset($objects, $limit, $offset)
    {
        return $objects->setLimit($limit)->setOffset($offset);
    }

    /**
     * @see parent
     */
    protected function _sendTest($registration, $data)
    {
        return array();
    }

    /**
     * Gets all of the generators from a registration
     *
     * @return array
     */
    protected function _registeredGenerators($registration, $data)
    {
        $generators = array();
        if (!empty($data['importSelected'])) {
            foreach (explode(',', $data['importSelected']) as $generatorId) {
                $generator = $this->_manager->getById($generatorId, true);
                if (empty($generator)) {
                    continue;
                }
                $generators[] = $generator;
            }
        }
        return $generators;
    }

    /**
     * Gets coupon pools or codes from the platform
     *
     * @param Brontosoftware_Magento_Connector_Discovery_Source $source
     * @param boolean $onlyPools
     */
    protected function _pullCoupons($source, $onlyPools)
    {
        $results = array();
        foreach ($this->_rules->getBySource($source, $onlyPools) as $rule) {
            $type = $rule->getCouponType() == 3 ?
                $this->translate('Auto Generation') :
                $this->translate('Specific Coupon');
            if ($rule->getUseAutoGeneration()) {
                $type .= " ({$this->translate("Auto")})";
            }
            $result = array(
                'id' => $rule->getId(),
                'name' => $rule->getName(),
                'active' => $rule->getIsActive() ?
                    $this->translate('Yes') :
                    $this->translate('No')
            );
            if ($onlyPools) {
                $result['type'] = $type;
            } else {
                $result['code'] = $rule->getPrimaryCoupon()->getCode();
            }
            $results[] = $result;
        }
        $source->setResults($results);
    }

}