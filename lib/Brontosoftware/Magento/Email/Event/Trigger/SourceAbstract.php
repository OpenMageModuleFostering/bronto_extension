<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Magento/Email/Event/Trigger/SourceAbstract.php
 */

abstract class Brontosoftware_Magento_Email_Event_Trigger_SourceAbstract implements Brontosoftware_Magento_Connector_Event_SourceInterface
{
    protected $_trigger;
    protected $_message;
    protected $_helper;
    protected $_currencies;
    protected $_currency;
    protected $_config;
    protected $_settings;

    /**
     * @param Brontosoftware_Magento_Email_SettingsInterface $settings
     * @param Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface $currencies
     * @param Brontosoftware_Magento_Email_TriggerInterface $trigger
     * @param Brontosoftware_Magento_Order_SettingsInterface $helper
     * @param Brontosoftware_Magento_Core_Config_ScopedInterface $config
     * @param array $message
     */
    public function __construct(
        Brontosoftware_Magento_Email_SettingsInterface $settings,
        Brontosoftware_Magento_Core_Directory_CurrencyManagerInterface $currencies,
        Brontosoftware_Magento_Email_TriggerInterface $trigger,
        Brontosoftware_Magento_Order_SettingsInterface $helper,
        Brontosoftware_Magento_Core_Config_ScopedInterface $config,
        array $message
    ) {
        $this->_settings = $settings;
        $this->_currencies = $currencies;
        $this->_trigger = $trigger;
        $this->_message = $message;
        $this->_helper = $helper;
        $this->_config = $config;
    }

    /**
     * @see parent
     */
    public function getEventType()
    {
        return 'delivery';
    }

    /**
     * @see parent
     */
    public function action($model)
    {
        return 'add';
    }

    /**
     * @param string $name
     * @param mixed $content
     * @return array
     */
    protected function _createField($name, $content)
    {
        return array(
            'name' => $name,
            'content' => $content,
            'type' => 'html'
        );
    }

    /**
     * Sets the currency to be used in locale formatting
     *
     * @param string $code
     * @return $this
     */
    protected function _setCurrency($code)
    {
        $this->_currency = $this->_currencies->getByCode($code);
        return $this;
    }

    /**
     * Gets a tuple for the sender of this message
     *
     * @param mixed $store
     * @return array
     */
    protected function _sender($store)
    {
        $sender = $this->_message['sender'];
        $senderNamePath = 'trans_email/ident_' . $sender . '/name';
        $senderEmailPath = 'trans_email/ident_' . $sender . '/email';
        return array(
            $this->_config->getValue($senderNamePath, 'store', $store),
            $this->_config->getValue($senderEmailPath, 'store', $store)
        );
    }

    /**
     * Gets a boilerplat delivery for the store
     *
     * @param string $email
     * @param mixed $store
     * @param string $type
     * @return array
     */
    protected function _createDelivery($email, $store, $type = 'triggered')
    {
        list($fromName, $fromEmail) = $this->_sender($store);
        $delivery = array(
            'start' => date('c'),
            'type' => $type,
            'messageId' => $this->_message['messageId'],
            'fromName' => $fromName,
            'fromEmail' => $fromEmail,
            'replyEmail' => empty($this->_message['replyTo']) ?
                $fromEmail :
                $this->_message['replyTo'],
        );
        foreach ($this->_message['sendFlags'] as $flag) {
            $delivery[$flag] = true;
        }
        $recipients = array(
            array(
                'id' => $email,
                'type' => 'contact',
                'deliveryType' => 'selected'
            )
        );
        if (isset($this->_message['exclusionLists'])) {
            foreach ($this->_message['exclusionLists'] as $listId) {
                $recipients[] = array(
                    'id' => $listId,
                    'type' => 'list',
                    'deliveryType' => 'excluded'
                );
            }
        }
        $delivery['recipients'] = $recipients;
        return $delivery;
    }

    /**
     * Generates a coupon code from the message
     *
     * @return array
     */
    protected function _extraFields($templateVars = array())
    {
        return $this->_settings->getExtraFields($this->_message, $templateVars, false);
    }

    /**
     * Gets the product based fields for a line item
     *
     * @param mixed $lineItem
     * @param int $inclTax
     * @param mixed $index
     * @return array
     */
    protected function _createLineItemFields($lineItem, $inclTax, $index = null)
    {
        $fields = array();
        $i = is_null($index) ? '' : "_{$index}";
        $productUrl = $this->_helper->getItemUrl($lineItem);
        if (array_key_exists('reviewForm', $this->_message)) {
            $productUrl .= $this->_message['reviewForm'];
        }
        $fields[] = $this->_createField("productId{$i}", $lineItem->getProductId());
        $fields[] = $this->_createField("productName{$i}", $this->_helper->getItemName($lineItem));
        $fields[] = $this->_createField("productSku{$i}", $lineItem->getSku());
        $fields[] = $this->_createField("productImgUrl{$i}", $this->_helper->getItemImage($lineItem));
        $fields[] = $this->_createField("productUrl{$i}", $productUrl);
        $fields[] = $this->_createField("productQty{$i}", number_format(is_null($lineItem->getQtyOrdered()) ? $lineItem->getQty() : $lineItem->getQtyOrdered(), 2));
        $fields[] = $this->_createField("productDescription{$i}", $this->_helper->getItemDescription($lineItem));
        $price = $this->_formatPrice($this->_helper->getItemPrice($lineItem, true));
        $rowTotal = $this->_formatPrice($this->_helper->getItemRowTotal($lineItem, true));
        $fields[] = $this->_createField("productPrice{$i}", $price);
        $fields[] = $this->_createField("productTotal{$i}", $rowTotal);
        $fields[] = $this->_createField("productPriceExclTax{$i}", $price);
        $fields[] = $this->_createField("productTotalExclTax{$i}", $rowTotal);
        if ($inclTax != 1) {
            if ($lineItem->getParentItemId()) {
                $lineItem = $lineItem->getParentItem();
            }
            $fields[] = $this->_createField("productPriceInclTax{$i}", $this->_formatPrice($lineItem->getPriceInclTax()));
            $fields[] = $this->_createField("productTotalInclTax{$i}", $this->_formatPrice($lineItem->getRowTotalInclTax()));
        } else {
            $fields[] = $this->_createField("productPriceInclTax{$i}", $price);
            $fields[] = $this->_createField("productTotalInclTax{$i}", $rowTotal);
        }
        return $fields;
    }

    /**
     * Formats the price using whatever code the price was
     * placed in
     *
     * @param float $price
     * @return string
     */
    protected function _formatPrice($price)
    {
        if (!is_null($this->_currency)) {
            $options = array(
                'precision' => 2,
                'display' => $this->_message['displaySymbol'] === false ?
                    Zend_Currency::NO_SYMBOL :
                    Zend_Currency::USE_SYMBOL
            );
            return $this->_currency->formatTxt($price, $options);
        }
        return $price;
    }
}
