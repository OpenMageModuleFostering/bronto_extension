<?php

class Brontosoftware_Email_Model_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    const API_TAG_PATTERN = "/#%%#([^_]+)_(.*?)#%%#/s";

    protected $_replaceTags = false;
    protected $_fields = array();
    protected $_eventFilters = array();
    protected $_indexes = array();
    protected $_conditionals = array();
    protected $_cssInliner = false;
    protected $_forceReplace = false;
    protected $_childFilter;

    /**
     * @see parent
     */
    public function setDesignParams($arr)
    {
        return $this;
    }

    /**
     * @see parent
     */
    public function setChildFilter($childFilter)
    {
        $this->_childFilter = $childFilter;
        $this->_childFilter->setForceReplace(true);
        return $this;
    }

    /**
     * @see parent
     */
    public function getChildFilter()
    {
        return is_null($this->_childFilter) ? $this : $this->_childFilter;
    }

    /**
     * @see parent
     */
    public function setForceReplace($forceReplace)
    {
        $this->_forceReplace = $forceReplace;
        return $this;
    }

    /**
     * Sets a callback to be run post filter to inline css
     *
     * @param callable $cssInliner
     * @return $this
     */
    public function setCssInliner($cssInliner)
    {
        $this->_cssInliner = $cssInliner;
        return $this;
    }

    /**
     * @see parent
     */
    public function blockDirective($construction)
    {
        $return = parent::blockDirective($construction);
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['id'])) {
            $safeName = $this->_charToCamel($params['id']);
            $this->_fields[$safeName] = $return;
            return $this->_returnOrReplace($safeName, $return);
        } else {
            return $this->_addField('block', $return);
        }
    }

    /**
     * @see parent
     */
    public function layoutDirective($construction)
    {
        $return = parent::layoutDirective($construction);
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['handle'])) {
            $safeName = $this->_charToCamel($params['handle']);
            $this->_fields[$safeName] = $return;
            return $this->_returnOrReplace($safeName, $return);
        } else {
            return $this->_addField('layout', $return);
        }
    }

    /**
     * @see parent
     */
    public function skinDirective($construction)
    {
        return $this->_addField('view', parent::skinDirective($construction));
    }

    /**
     * @see parent
     */
    public function mediaDirective($construction)
    {
        return $this->_addField('media', parent::mediaDirective($construction));
    }

    /**
     * @see parent
     */
    public function storeDirective($construction)
    {
        return $this->_addField('storeUrl', parent::storeDirective($construction));
    }

    /**
     * @see parent
     */
    public function protocolDirective($construction)
    {
        return $this->_addField('storeUrl', parent::protocolDirective($construction));
    }

    /**
     * @see parent
     */
    public function varDirective($construction)
    {
        $return = parent::varDirective($construction);
        if ($return == $construction[0]) {
            return $return;
        }
        list($directive, $modifiers) = explode('|', $construction[2], 2);
        $safeName = $this->_varReplacement($directive);
        $this->_fields[$safeName] = $return;
        return $this->_returnOrReplace($safeName, $return);
    }

    /**
     * @see parent
     */
    public function configDirective($construction)
    {
        $return = parent::configDirective($construction);
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['path'])) {
            $parts = explode('/', $params['path']);
            $parts = array_slice($parts, 1);
            $safeName = $this->_charToCamel(implode('_', $parts));
            $this->_fields[$safeName] = $return;
            return $this->_returnOrReplace($safeName, $return);
        }
        return $return;
    }

    /**
     * @see parent
     */
    public function customvarDirective($construction)
    {
        $return = parent::customvarDirective($construction);
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['code'])) {
            $this->_fields[$params['code']] = $return;
            return $this->_returnOrReplace($params['code'], $return);
        }
        return $return;
    }

    /**
     * @see parent
     */
    public function dependDirective($construction)
    {
        $return = parent::dependDirective($construction);
        $safeName = $this->_varReplacement($construction[1], 'depends');
        $this->_fields[$safeName] = $this->getChildFilter()->filter($return);
        return $this->_returnOrReplace($safeName, $this->_fields[$safeName]);
    }

    /**
     * @see parent
     */
    public function ifDirective($construction)
    {
        $return = parent::ifDirective($construction);
        $safeName = $this->_varReplacement($construction[1], 'if');
        $this->_fields[$safeName] = $this->getChildFilter()->filter($return);
        return $this->_returnOrReplace($safeName, $this->_fields[$safeName]);
    }

    /**
     * Sets the replacement option to replace to tags only
     *
     * @param boolean $replaceTags
     * @return $this
     */
    public function setReplaceToTags($replaceTags)
    {
        $this->_replaceTags = $replaceTags;
        return $this;
    }

    /**
     * Add an event filter to be triggered on flush
     *
     * @param mixed $filter
     * @return $this
     */
    public function addEventFilter($filter)
    {
        $this->_eventFilters[] = $filter;
        return $this;
    }

    /**
     * Finish applying the filter on the context
     *
     * @param array $templateData
     * @return array
     */
    public function applyAndTransform($templateData)
    {
        $this->_fillContext($templateData);
        $appliedFields = array();
        foreach ($this->_getContext($templateData, false) as $name => $content) {
            $appliedFields[] = array(
                'name' => $name,
                'content' => $content,
                'type' => 'html'
            );
        }
        return $appliedFields;
    }

    /**
     * @see parent
     */
    public function getContext($message)
    {
        return $this->_getContext($message, true);
    }

    /**
     * @see parent
     */
    public function filter($html)
    {
        $html = parent::filter($html);
        if ($this->_cssInliner) {
            $this->_cssInliner->setInlineCssFile($this->getInlineCssFile());
            $html = $this->_cssInliner->getPreparedTemplateText($html);
        }
        if (!$this->_forceReplace && !$this->_replaceTags) {
            if (preg_match_all(self::API_TAG_PATTERN, $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $this->_fields[$match[1]] = $match[2];
                }
            }
        }
        return $html;
    }

    /**
     * Gets all of the API tgs created from the mesage text
     *
     * @return array
     */
    public function getReplacedTags()
    {
        return array_keys($this->_fields);
    }

    /**
     * Replaces any delimited string to a CamelCased string
     *
     * @param string $string
     * @param string $split
     * @return string
     */
    protected function _charToCamel($string, $split = '_')
    {
        $parts = explode($split, $string);
        $afterFirst = array_slice($parts, 1);
        $afterFirst = array_map('ucfirst', $afterFirst);
        $name = implode('', array_merge([$parts[0]], $afterFirst));
        return substr($name, 0, 25);
    }

    /**
     * Implementation of template processors
     *
     * @param array $message
     * @param boolean $forceContext
     * @return array
     */
    protected function _getContext($message, $forceContext)
    {
        $fields = $this->_fields;
        foreach ($this->_eventFilters as $eventFilter) {
            $fields += $eventFilter->apply($message, $this->_templateVars, $forceContext);
        }
        return $fields;
    }

    /**
     * Replaces magento variables with API friendly names
     *
     * @param string $string
     * @param int $prefix
     * @param string $value to replace
     * @return string
     */
    protected function _varReplacement($string, $prefix = '', $value = null)
    {
        $safeName = preg_replace('/(?:\.get|\.|\,|\'|\"|\/)/', '_', $string);
        $safeName = preg_replace('/\([^\)]*\)/', '', $safeName);
        $safeName = preg_replace('/[\$\(\)\s]/', '', $safeName);
        $safeName = substr($this->_charToCamel($safeName), 0, 25 - (strlen($prefix) + 1));
        if (!empty($prefix)) {
            $safeName = $prefix . ucfirst($safeName);
        }
        $return = $safeName;
        $index = 1;
        while (array_key_exists($return, $this->_fields)) {
            if ($value && $this->_fields[$return] === $value) {
                break;
            }
            $index++;
            $return = "{$safeName}{$index}";
        }
        return $return;
    }

    /**
     * Returns or replaces the content with API tags
     *
     * @param string $safeName
     * @param string $return
     * @return string
     */
    protected function _returnOrReplace($safeName, $return)
    {
        if ($this->_replaceTags) {
            return "%%#{$safeName}%%";
        } else if ($this->_forceReplace) {
            return $return;
        } else {
            return "#%%#{$safeName}_{$return}#%%#";
        }
    }

    /**
     * Adds filtered variable to fields
     *
     * @param string $index
     * @param string $output
     * @return string
     */
    protected function _addField($index, $output)
    {
        if (!array_key_exists($index, $this->_indexes)) {
            $this->_indexes[$index] = 0;
        }
        $safeName = "{$index}Index{$this->_indexes[$index]}";
        $this->_fields[$safeName] = $output;
        $this->_indexes[$index]++;
        return $this->_returnOrReplace($safeName, $output);
    }

    /**
     * Fills the remaining context with other potential values
     *
     * @param array $templateData
     * @return void
     */
    protected function _fillContext($templateData)
    {
        /**
        $filters = $templateData['filters'];
        foreach ($this->templateVars as $name => $var) {
            if (is_string($var)) {
                $this->_fields[$this->_charToCamel($name)] = $var;
                continue;
            }
            if (array_key_exists($name, $filters)) {
                list($fieldName, $fieldValue) = call_user_func($filters[$name], $var);
                $this->_fields[$fieldName] = $fieldValue;
            }
        }
         */
    }
}
