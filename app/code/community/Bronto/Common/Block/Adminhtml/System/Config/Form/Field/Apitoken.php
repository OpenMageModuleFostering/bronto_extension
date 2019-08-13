<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2013 Bronto Software, Inc.
 * @version   1.6.7
 */
class Bronto_Common_Block_Adminhtml_System_Config_Form_Field_Apitoken extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Get element ID of the dependent field's parent row
     *
     * @param object $element
     * @return String
     */
    protected function _getRowElementId($element)
    {
        return 'row_' . $element->getId();
    }

    /**
     * Override method to output our custom HTML with JavaScript
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $_html = array();

        // Create form object to grab scope details
        $form = new Mage_Adminhtml_Block_System_Config_Form;
        $scope = $form->getScope();
        $scopeId = $form->getScopeId();

        $element->setData('onchange', "validateToken(this.form, this);");
        $element->setData('after_element_html', "
            <span id=\"loadingmask\" style=\"display: none; width: 100px;\">
                <span class=\"loader\" id=\"loading-mask-loader\" style=\"background: url(" . $this->getSkinUrl('bronto/images/ajax-loader-tr.gif') . ") no-repeat 0 50%; background-size: 20px; padding:3px 0 3px 25px;\">" . $this->__(' Verifying...') . "</span>
                <span id=\"loading-mask\"></span>
            </span>
            <script>
                function toggleDisabled(form, element) {
                    var statusText = $('bronto-validation-status');
                    var disabled = (statusText.className == 'invalid' || statusText.className == '');

                    for (i = 0; i < form.length; i++) {
                        if (form.elements[i].id != '{$element->getId()}' &&
                            form.elements[i].type != 'hidden' &&
                            form.elements[i].name.indexOf('groups') == 0) {
                            form.elements[i].disabled = disabled;
                        }
                    }
                    var last = element.parentNode.lastChild;
                    if (last.className == 'note') {
                        last.innerHTML = '';
                    }

                    var buttonP = document.getElementById('verify-button');
                    console.log(buttonP);
                    console.log(buttonP.children[0]);
                    for (i = 0; i < buttonP.children.length; i++) {
                        console.log(buttonP.children[i]);
                        console.log(i);
                        if (disabled) {
                            $(buttonP.children[i]).addClassName('disabled');
                        } else {
                            $(buttonP.children[i]).removeClassName('disabled');
                        }
                        buttonP.children[i].disabled = disabled;
                    }

                }
                
                function trim1 (str) {
                    return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
                }

                function validateToken(form, element) {
                    var token      = trim1($('{$element->getId()}').value);
                    var statusText = $('bronto-validation-status');
                    var reloadurl  = '{$this->getUrl('*/apitoken/ajaxvalidation')}';
                    
                    statusText.innerHTML = $('loadingmask').innerHTML;
                    statusText.removeClassName('valid').removeClassName('invalid');

                    new Ajax.Request(reloadurl, {
                        method: 'post',
                        parameters: {token: token, scope: '{$scope}', scopeid: '{$scopeId}'},
                        onComplete: function(transport) {
                            Element.hide('loadingmask');
                            if (transport.responseText == '\"Passed Verification\"') {
                                statusText.innerHTML = 'Passed Verification';
                                statusText.addClassName('valid');
                            } else if (transport.responseText == '\"Failed Verification\"') {
                                statusText.innerHTML = 'Failed Verification';
                                statusText.addClassName('invalid');
                            } else {
                                statusText.innerHTML = 'No Token Provided';
                            }
                            
                            toggleDisabled(form, element);
                        }
                    });
                    
                    return false;
                }
            </script>
        ");

        if (!$this->helper('bronto_common')->getApiToken()) {
            $element->setComment('<span style="color:red;font-weight:bold">Please enter your Bronto API key here.</span>');
            $buttonHtml = "";
        } else {
            $button = $this->getLayout()
                ->createBlock('bronto_verify/adminhtml_widget_button_runtoken')
                ->toHtml();
            $buttonHtml = "<p class=\"form-buttons\" id=\"verify-button\">{$button}</p>";
        }

        // Show Roundtrip Install Verification Status
        $_html[] = '<style>' .
            '#bronto-validation-status { color:grey; font-weight:bold; }' .
            '#bronto-validation-status.valid { color: green; }' .
            '#bronto-validation-status.invalid { color: red; }' .
            '</style>' . '<strong style="float: left; width: 80px">Last Status:</strong> ' .
            $this->helper('bronto_verify/apitoken')->getAdminScopedApitokenStatusText() . $buttonHtml;

        // Show everything Else
        if (!empty($_html)) {
            $elementHtml = $element->getElementHtml();
            if ($element->getComment()) {
                $elementHtml.= '<p class="note"><span>'.$element->getComment().'</span></p>';
                $element->setComment(null);
            }
            $elementHtml .= '<div style="margin-top:10px">';
            $elementHtml .= implode('<br />', $_html);
            $elementHtml .= '</div>';
            return $elementHtml;
        }

        return parent::_getElementHtml($element);
    }
}
