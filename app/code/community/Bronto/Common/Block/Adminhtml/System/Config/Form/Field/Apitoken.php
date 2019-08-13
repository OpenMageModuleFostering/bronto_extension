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

        if (!Mage::helper('bronto_common')->getApiToken()) {
            $element->setComment('<span style="color:red;font-weight:bold">Please enter your Bronto API key here.</span>');
            $element->setData('onchange', "toggleDisabled(this.form, this);");
            $element->setData('after_element_html', "
                <script>
                function toggleDisabled(form, element) {
                    var disabled = (element.value.length < 36);
                    for (i = 0; i < form.length; i++) {
                        if (form.elements[i].id != '{$element->getId()}' &&
                            form.elements[i].type != 'hidden' &&
                            form.elements[i].name.indexOf('groups') == 0) {
                            console.log(form.elements[i]);
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
                        }
                        buttonP.children[i].disabled = disabled;
                    }
                }
                </script>
            ");

            $button = $this
                ->getLayout()
                ->createBlock('bronto_roundtrip/adminhtml_widget_button_run')
                ->setData('disabled', 'disabled')
                ->toHtml()
            ;
        } else {
            try {
                $button = $this
                    ->getLayout()
                    ->createBlock('bronto_roundtrip/adminhtml_widget_button_run')
                    ->toHtml()
                ;

                $organization = null;
                $name         = null;
                $email        = null;

                /* @var $loginObject Bronto_Api_Login */
                $loginObject = Mage::helper('bronto_common')->getApi()->getLoginObject();
                $iterator    = $loginObject->readAll()->iterate();
                foreach ($iterator as $login /* @var $login Bronto_Api_Login_Row */) {
                    if ($iterator->count() == 1) {
                        if (isset($login->contactInformation->organization)) {
                            $organization = $login->contactInformation->organization;
                        }
                        if (isset($login->contactInformation->firstName)) {
                            $name = trim($login->contactInformation->firstName);
                        }
                        if (isset($login->contactInformation->lastName)) {
                            $name .= trim(' ' . $login->contactInformation->lastName);
                        }
                        if (isset($login->contactInformation->email)) {
                            $email = trim($login->contactInformation->email);
                        }
                    } else {
                        if (isset($login->contactInformation->organization)) {
                            if (strlen($login->contactInformation->organization) > $organization) {
                                $organization = $login->contactInformation->organization;
                            }
                        }
                    }
                }

                if (!empty($organization)) {
                    $_html[] = '<strong style="float: left; width: 88px">Organization:</strong> ' . $organization;
                }

                if (!empty($name)) {
                    $_html[] = '<strong style="float: left; width: 88px">Name:</strong> ' . $name;
                }

                if (!empty($email)) {
                    $_html[] = '<strong style="float: left; width: 88px">Email:</strong> ' . $email;
                }
            } catch (Exception $e) {
                //
            }
        }

        // Show Roundtrip Install Verification Status
        $buttonHtml = "<p class=\"form-buttons\" id=\"verify-button\">{$button}</p>";
        $_html[] = '<strong style="float: left; width: 88px">Install Status:</strong> ' .
                Mage::helper('bronto_roundtrip')->getRoundtripStatusText() . $buttonHtml;

        // Show everything Else
                if (!empty($_html)) {
                    $elementHtml  = $element->getElementHtml();
                    $elementHtml .= '<div style="margin-top:10px">';
                    $elementHtml .= implode('<br />', $_html);
                    $elementHtml .= '</div>';
                    return $elementHtml;
        }

        return parent::_getElementHtml($element);
    }
}
