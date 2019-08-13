<?php

class Brontosoftware_Connector_Block_Registration_Edit_Form_Element_Scope extends Varien_Data_Form_Element_Abstract
{
    private $_previous = null;
    private $_scopeTrees;

    /**
     * @see parent
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        if (array_key_exists('scopeHash', $attributes)) {
            $this->_previous = $attributes['scopeHash'];
        }
        $this->_scopeTrees = [];
        $middleware = Mage::getModel('brontosoftware_connector/impl_connector_middleware');
        $active = Mage::getModel('brontosoftware_connector/registration')
            ->getCollection()
            ->filterByActive();
        foreach ($active as $registration) {
            if ($registration->getScopeHash(true) === $this->_previous) {
                continue;
            }
            $this->_scopeTrees[] = array(
                $registration,
                $middleware->scopeTree($registration)
            );
        }
    }

    /**
     * @see parent
     */
    public function getElementHtml()
    {
        return $this->_printCheckboxTree() . $this->getAfterElementHtml();
    }

    /**
     * Prints the checkbox on the generated tree
     *
     * @param string $type
     * @param mixed $node
     * @return string
     */
    protected function _printCheckbox($type, $node)
    {
        $scopeHash = "{$type}.{$node->getId()}";
        $scopeId = "{$scopeHash}.{$node->getCode()}";
        foreach ($this->_scopeTrees as list($registration, $scopeTree)) {
            $holderName = "{$registration->getId()}:{$registration->getName()}";
            switch ($registration->getScope()) {
            case 'default':
                return "<label>{$node->getName()} [$holderName]</label>";
            case 'store':
                if ($type == 'website') {
                    foreach ($node->getStores() as $store) {
                        if ($store->getId() == $registration->getScopeId()) {
                            return "<label>{$node->getName()} [NA]</label>";
                        }
                    }
                }
            case 'website':
                if ($type == 'default') {
                    return "<label>{$node->getName()} [NA]</label>";
                } else if ($type == 'store' && $registration->getScope() == 'website' && $registration->getScopeId() == $node->getWebsiteId()) {
                    return "<label>{$node->getName()} [$holderName]</label>";
                }
            default:
                if ($scopeHash == $scopeTree['id']) {
                    return "<label>{$node->getName()} [$holderName]</label>";
                }
            }
        }
        $selected = $scopeId === $this->_previous ? 'CHECKED' : '';
        return "<label><input name='scopeHash' value='{$scopeId}' {$selected} type='radio'> {$node->getName()}</label>";
    }

    /**
     * Gets the Scope tree form element
     *
     * @return string
     */
    protected function _printCheckboxTree()
    {
        $default = new Varien_Object(array(
            'name' => 'Default',
            'code' => 'default',
            'id' => 0
        ));
        $html = '<ul><li>';
        $html .= $this->_printCheckbox('default', $default);
        foreach (Mage::app()->getWebsites() as $website) {
            $html .= '<ul style="margin-left:20px"><li>';
            $html .= $this->_printCheckbox('website', $website);
            $html .= '<ul style="margin-left:20px">';
            foreach ($website->getStores() as $store) {
                $html .= '<li>';
                $html .= $this->_printCheckbox('store', $store);
                $html .= '</li>';
            }
            $html .= '</ul></li></ul>';
        }
        return $html . '</li></ul>';
    }
}
