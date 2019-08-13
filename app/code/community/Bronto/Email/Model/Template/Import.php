<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2013 Bronto Software, Inc.
 * @version     1.1.1
 */
class Bronto_Email_Model_Template_Import extends Mage_Core_Model_Email_Template
{
    /**
     * @var array
     */
    private $_templateCollection;

    /**
     * @var [type]
     */
    private $_apiToken;

    /**
     * @var [type]
     */
    private $_apiObject;

    public function _construct()
    {
        // Verify requirements
        if (!extension_loaded('soap') || !extension_loaded('openssl') ||
            !$this->_apiToken = Mage::helper('bronto_common')->getApiToken()) {
            if (Mage::helper('bronto_email')->isEnabled()) {
                Mage::helper('bronto_email')->disableModule();
            }
        }

        parent::_construct();
    }

    public function loadTemplateCollection()
    {
        if ($this->_apiToken) {
            $this->_templateCollection = Mage::getResourceSingleton('core/email_template_collection');
        }
        return $this;
    }

    public function importTemplates()
    {
        $allStores = Mage::app()->getStores();
        
        //process existing
        $token = Mage::helper('bronto_common')->getApiToken();
        if($token) {
            $this->_apiObject = new Bronto_Api_Message(array(
                    'api' => new Bronto_Api($token)
            ));

            //process existing
            $this->loadTemplateCollection();
            foreach ($this->_templateCollection as $template) {
                $template->setStoreId(1);
                $this->processMessage($template);
            }
        }
        
        //process defaults
        foreach (array_keys($allStores) as $_eachStoreId)
        {
            $_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();

            $token = Mage::helper('bronto_common')->getApiToken($_storeId);
            if($token) {
                $this->_apiObject = new Bronto_Api_Message(array(
                        'api' => new Bronto_Api($token)
                ));

                //process default
                $templates = Mage::getModel('bronto_common/email_message')->getDefaultTemplates();
                foreach(array_keys($templates) as $templateToLoad) {
                    $template = Mage::getModel('bronto_common/email_message');
                    $template->loadDefault($templateToLoad);
                    $template->setOrigTemplateCode($templateToLoad);
                    $template->setTemplateCode($_storeCode . '_' . $templateToLoad);
                    $template->setAddedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    //$template->unsTemplateId();
                    $template->setStoreId($_storeId);
                    $this->processMessage($template);
                }
            }
        }

        return $this;
    }

    protected function processMessage($template)
    {
        $data = $template->getData();
        $emt  = Mage::getModel('bronto_common/email_message_templatefilter');

        if(!isset($data['bronto_message_id']) || $data['bronto_message_id'] == '') {
            try{
                // Send message template to Bronto
                $message = new Bronto_Api_Message_Row(array(
                    'apiObject' => $this->_apiObject
                ));

                // Add Check for required fields
                if (array_key_exists('template_text', $data) && array_key_exists('template_subject', $data)) {                
                    $message->name = $data['template_code'];
                    $message->status = 'active';
                    
                    // Define variables for filtered Subject and Text
                    $templateSubject = $emt->filter($data['template_subject']);
                    $templateText    = $emt->filter($data['template_text']);
                    $templateTextRip = $emt->filter($this->ripTags($data['template_text']));
                    
                    // Template has invalid or missing required attributes
                    if ('' == $templateSubject || '' == $templateText || '' == $templateTextRip) {
                        return;
                    }
                    
                    $message->content = array(
                        array(
                            'type' => 'html',
                            'subject' => $templateSubject,
                            'content' => $templateText,
                        ),
                        array(
                            'type' => 'text',
                            'subject' => $templateSubject,
                            'content' => $templateTextRip,
                        )
                    );
                    $message->subject = $templateSubject;
                    $message->save();
                    $template->setBrontoMessageId($message->id);
                    $template->setBrontoMessageName($message->name);
                    $template->setBrontoMessageApproved(0);
                    $template->save();
                }
            }
            catch(Exception $e) {
                Mage::log('Bronto Import:' . $e->getMessage());
            }
        }
    }

    protected function ripTags($string)
    {
        $string = preg_replace ('/<[^>]*>/', ' ', $string);
        // ----- remove multiple spaces -----
        $string = trim(preg_replace('/ {2,}/', ' ', $string));
        return $string;
    }
}
