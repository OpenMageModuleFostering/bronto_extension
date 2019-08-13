<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Email/Event/Trigger/Reorder.php
 */

class Brontosoftware_Magento_Email_Event_Trigger_Reorder extends Brontosoftware_Magento_Email_Event_Trigger_OrderBasedAbstract
{
    protected $_stockManager;

    /**
     * @param Brontosoftware_Magento_Core_Stock_ManagerInterface $stockManager
     * @param Brontosoftware_Magento_Email_SettingsInterface $settings
     * @param Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface $currencies
     * @param Brontosoftware_Magento_Email_TriggerInterface $trigger
     * @param Brontosoftware_Magento_Order_SettingsInterface $helper
     * @param Brontosoftware_Magento_Core_Config_ScopedInterface $config
     * @param Brontosoftware_Magento_Core_Sales_AddressRenderInterface $addressRender
     * @param array $message
     */
    public function __construct(
        Brontosoftware_Magento_Core_Stock_ManagerInterface $stockManager,
        Brontosoftware_Magento_Email_SettingsInterface $settings,
        Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface $currencies,
        Brontosoftware_Magento_Email_TriggerInterface $trigger,
        Brontosoftware_Magento_Order_SettingsInterface $helper,
        Brontosoftware_Magento_Core_Config_ScopedInterface $config,
        Brontosoftware_Magento_Core_Sales_AddressRenderInterface $addressRender,
        array $message
    ) {
        parent::__construct(
            $settings,
            $currencies,
            $trigger,
            $helper,
            $config,
            $addressRender,
            $message);
        $this->_stockManager = $stockManager;
    }

    /**
     * @see parent
     */
    public function action($lineItem)
    {
        $stock = $this->_stockManager->getByProductId($lineItem->getProductId(), $lineItem->getStoreId());
        if (empty($stock) || !$stock->getIsInStock()) {
            return '';
        }
        return 'add';
    }

    /**
     * @see parent
     */
    public function transform($lineItem)
    {
        $order = $lineItem->getOrder();
        $store = $order->getStore();
        $inclTax = (int)$this->_config->getValue(self::XML_PATH_PRICE_DISPLAY, 'store', $store);
        $delivery = $this->_createDelivery($order->getCustomerEmail(), $store);
        $fields = array_merge(
            $this->_createOrderFields($order, $store),
            $this->_createLineItemFields($lineItem, $inclTax),
            $this->_extraFields(array('order' => $order)));
        $delivery['fields'] = $fields;
        return $delivery;
    }
}