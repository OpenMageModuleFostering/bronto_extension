<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Connector/Event/PushLogic.php
 */

class Brontosoftware_Magento_Connector_Event_PushLogic
{
    protected $_queueManager = null;
    protected $_connector = null;
    protected $_helper = null;
    protected $_platform = null;
    protected $_source = null;
    protected $_context = null;

    /**
     * @param Brontosoftware_Magento_Connector_QueueManagerInterface $queueManager
     * @param Brontosoftware_Magento_Connector_SettingsInterface $connector
     * @param Brontosoftware_Magento_Connector_Event_HelperInterface $helper
     * @param Brontosoftware_Magento_Connector_Event_PlatformInterface $platform
     * @param Brontosoftware_Magento_Connector_Event_SourceInterface $source
     * @param Brontosoftware_Magento_Connector_Event_ContextProviderInterface $context
     */
    public function __construct(
        Brontosoftware_Magento_Connector_QueueManagerInterface $queueManager,
        Brontosoftware_Magento_Connector_SettingsInterface $connector,
        Brontosoftware_Magento_Connector_Event_HelperInterface $helper,
        Brontosoftware_Magento_Connector_Event_PlatformInterface $platform,
        Brontosoftware_Magento_Connector_Event_SourceInterface $source,
        Brontosoftware_Magento_Connector_Event_ContextProviderInterface $context = null
    ) {
        $this->_queueManager = $queueManager;
        $this->_connector = $connector;
        $this->_helper = $helper;
        $this->_platform = $platform;
        $this->_source = $source;
        // This isn't great, but works for now
        if (is_null($context) && $source instanceof Brontosoftware_Magento_Connector_Event_ContextProviderInterface) {
            $context = $source;
        }
        $this->_context = $context;
    }

    /**
     * Does the appropriate push on the object
     *
     * @param mixed $object
     * @param mixed $storeId
     * @param boolean $foreground
     * @param boolean $fallbackPersist
     * @return boolean
     */
    public function pushEvent($object, $storeId = null, $foreground = true, $fallbackPersist = true)
    {
        if (!$this->_connector->isTestMode('store', $storeId) && $this->_helper->isEnabled('store', $storeId)) {
            $action = $this->_source->action($object);
            if (!empty($action)) {
                if ($foreground && $this->_connector->isEventQueued('store', $storeId)) {
                    $event = $this->_platform->annotate(new Brontosoftware_Magento_Connector_Event_QueuableSource($this->_source, $this->_context), $object, $action, $storeId);
                    return $this->_queueManager->save($event);
                } else {
                    $event = $this->_platform->annotate($this->_source, $object, $action, $storeId);
                    return $this->_platform->dispatch($event) || (
                        $fallbackPersist &&
                        $this->_queueManager->save($event)
                    );
                }
            }
        }
        return false;
    }
}
