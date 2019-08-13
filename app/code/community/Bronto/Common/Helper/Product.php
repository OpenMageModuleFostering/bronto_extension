<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Helper_Product extends Mage_Core_Helper_Abstract
{

    /**
     * @var array
     */
    protected $_productCache = array();

    /**
     * @var array
     */
    protected $_templateVars = array();

    /**
     * Transforms input string by replacing parameters in the
     * template string with corresponding values
     *
     * @link https://github.com/leek/zf-components/blob/master/library/Leek/Config.php
     * @param string $subject            Template string
     * @param array $map                Key / value pairs to substitute with
     * @param string $delimiter Template parameter delimiter (must be valid without escaping in a regular expression)
     * @param bool $blankIfNone        Set to blank if none found
     * @return string
     * @static
     */
    public function templatize($subject, $map, $delimiter = '%', $blankIfNone = false)
    {
        if ($matches = $this->getTemplateVariables($subject, $delimiter)) {
            $map = array_change_key_case($map, CASE_LOWER);
            foreach ($matches as $match) {
                if (isset($map[$match])) {
                    $subject = str_replace($delimiter . $match . $delimiter, $map[$match], $subject);
                } elseif ($blankIfNone) {
                    $subject = str_replace($delimiter . $match . $delimiter, '', $subject);
                }
            }
        }

        return $subject;
    }

    /**
     * @param string $subject
     * @param string $delimiter
     * @param mixed $index
     * @return array
     */
    public function getTemplateVariables($subject, $delimiter = '%')
    {
        if (!isset($this->_templateVars[$subject])) {
            $this->_templateVars[$subject] = array();
            if (preg_match_all('/' . $delimiter . '([a-z0-9_]+)' . $delimiter . '/', $subject, $matches)) {
                if ($matches[1]) {
                    $this->_templateVars[$subject] = $matches[1];
                }
            }
        }

        return $this->_templateVars[$subject];
    }

    /**
     * @param int $productId
     * @return boolean|Mage_Catalog_Model_Product
     */
    public function getProduct($productId, $storeId = false)
    {
        if (is_int($productId) || is_string($productId)) {
            if (isset($this->_productCache[$storeId][$productId])) {
                return $this->_productCache[$storeId][$productId];
            } elseif ($storeId) {
                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
            } else {
                $product = Mage::getModel('catalog/product')->load($productId);
            }
        } else {
            $product = $productId;
        }

        if (!is_object($product) || !($product instanceOf Mage_Catalog_Model_Product)) {
            return false;
        } else {
            $productId = $product->getId();
        }

        $this->_productCache[$storeId][$productId] = $product;
        return $product;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string $name
     * @return mixed
     */
    public function getProductAttribute($productId, $name, $storeId = false)
    {
        if ($product = $this->getProduct($productId, $storeId)) {
            try {
                switch ($name) {
                    case 'img':
                    case 'image':
                        return $product->getSmallImageUrl();
                    case 'url':
                        return Mage::helper('catalog/product')->getProductUrl($product);
                }

                $inputType = $product->getResource()
                    ->getAttribute($name)
                    ->getFrontend()
                    ->getInputType();

                switch ($inputType) {
                    case 'multiselect':
                    case 'select':
                    case 'dropdown':
                        $value = $product->getAttributeText($name);
                        if (is_array($value)) {
                            $value = implode(', ', $value);
                        }
                        break;
                    default:
                        $value = $product->getData($name);
                        break;
                }

                return $value;
            } catch (Exception $e) {
                //
            }
        }

        return false;
    }

}
