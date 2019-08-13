<?php

/**
 * Short description for file
 *
 * Long description (if any) ...
 *
 * PHP version 5
 *
 * The license text...
 *
 * @category  Bronto
 * @package   Roundtrip
 * @author    Adam Daniels <adam.daniels@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   CVS: $Id:$
 * @link      <>
 * @see       References to other sections (if any)...
 */

/**
 * Short description for class
 *
 * Long description (if any) ...
 *
 * @category  Bronto
 * @package   Roundtrip
 * @author    Adam Daniels <adam.daniels@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   Release: @package_version@
 * @link      <>
 * @see       References to other sections (if any)...
 */
class Bronto_Roundtrip_Model_Roundtrip
{
    //  {{{ properties

    /**
     * Description for const
     */
    const NOTICE_IDENTIFER = 'bronto_roundtrip';

    /**
     * Description for private
     * @var string
     * @access private
     */
    private $_sandboxApiToken = '077E2083-BBED-4B16-A3AB-E1095EAA2E58';
//    private $_sandboxApiToken = 'A02B75E2-D624-4404-AA28-44A306C08ECF';
//    private $_sandboxApiToken = '53873730-F77B-4B0D-9840-43F21846F991';


    /**
     * Description for private
     * @var string
     * @access private
     */
//    private $_sandboxEmail = 'adam.daniels+sandbox@atlanticbt.com';
    private $_sandboxEmail = 'ps-eng@bronto.com';
//    private $_sandboxEmail = 'chris.duffy@atlanticbt.com';

    //  }}}
    //  {{{ processRoundtrip()

    /**
     * @return array
     * @access public
     */
    public function processRoundtrip()
    {
        $status = true;
        $helper = Mage::helper(self::NOTICE_IDENTIFER);

		//  Run through all API's ups to ensure valid API tokens
        //
        //  keep the '/data' on the helper alias else EcomDev_PHPUnit will
        //  not properly replace the correct registry key for unit test which
        //  exerts test for gracefull failure recovery
        if (!Mage::helper('bronto_common/data')->validApiTokens()) {
            $helper->setRoundtripStatus($helper->getPath('sandbox_connect'), '0');
            return false;
        }

        // Try Connecting to Sandbox Account
        $api = $this->_testSandboxConnect();
        if ($api) {
            $helper->setRoundtripStatus($helper->getPath('sandbox_connect'), '1');
        } else {
            // if we can't get the api, we cant do anything else
            $helper->setRoundtripStatus($helper->getPath('sandbox_connect'), '0');
            return false;
        }

        // Try Creating Transactional Contact
        $contact = $this->_testCreateContact($api);
        if ($contact) {
            $helper->setRoundtripStatus($helper->getPath('sandbox_create_contact'), '1');
        } else {
            $helper->setRoundtripStatus($helper->getPath('sandbox_create_contact'), '0');
            $status = false;
        }

        // Try Creating Onboarding Contact
        if ($this->_testAddContactToList($api, $contact)) {
            $helper->setRoundtripStatus($helper->getPath('sandbox_change_contact'), '1');
        } else {
            $helper->setRoundtripStatus($helper->getPath('sandbox_change_contact'), '0');
            $status = false;
        }

        // Try to Import order for Contact
        if ($this->_testProcessOrder($api, $contact)) {
            $helper->setRoundtripStatus($helper->getPath('sandbox_import_order'), '1');
        } else {
            $helper->setRoundtripStatus($helper->getPath('sandbox_import_order'), '0');
            $status = false;
        }

        // Set setting and return results
        if ($status) {
            $helper->setRoundtripStatus($helper->getPath('status'), '1');
        } else {
            $helper->setRoundtripStatus($helper->getPath('status'), '0');
        }

        return $status;
    }

    //  }}}
    //  {{{ _assertContactAttribute()

    /**
     * Function to assert a value matches a contact attribute
     *
     * @param Bronto_Common_Model_Api $api
     * @param string                  $email
     * @param string                  $attribute
     * @param string                  $value
     *
     * @return boolean
     * @access private
     */
    private function _assertContactAttribute(
        Bronto_Common_Model_Api $api,
        $email,
        $attribute,
        $value
    ) {
        $contactObject = $api->getContactObject();

        // Load Contact
        $contact = $contactObject->createRow();
        $contact->email = $email;

        try {
            $contact->read();
        } catch (Exception $e) {
            // Contact doesn't exist
            Mage::helper('bronto_roundtrip')->writeDebug('could not read contact');
            return false;
        }

        if (!$contact->id) {
            Mage::helper('bronto_roundtrip')->writeDebug('could not find contact');
            return false;
        }

        if (!$contact->{$attribute} == $value) {
            Mage::helper('bronto_roundtrip')->writeDebug('could not assert that contact ' . $attribute . ': ' . $contact->{$attribute} . ' == ' . $value);
            return false;
        }

        Mage::helper('bronto_roundtrip')->writeDebug('Asserted that contact ' . $attribute . ': ' . $contact->{$attribute} . ' == ' . $value);
        return true;
    }

    //  }}}
    //  {{{ _testSandboxConnect()

    /**
     * Create api instance and attempt to log in
     *
     * @return boolean|Ambigous <Bronto_Common_Model_Api, multitype:>
     * @access protected
     */
    protected function _testSandboxConnect()
    {
        $api = Bronto_Common_Model_Api::getInstance($this->_sandboxApiToken);

        if (!$api->login()) {
            return false;
        }

        return $api;
    }

    //  }}}
    //  {{{ _testCreateContact()

    /**
     * Create a test Contact
     *
     * @param Bronto_Common_Model_Api $api
     *
     * @return boolean|Bronto_Api_Contact_Row
     * @access protected
     */
    protected function _testCreateContact(Bronto_Common_Model_Api $api)
    {
        $commonHelper = Mage::helper('bronto_common/contact');

        // Load Contact
        $contactObject = $api->getContactObject();
        $contact = $contactObject->createRow();
        $contact->email = $this->_sandboxEmail;

        // Get Contact Info
        try {
            $contact->read();
        } catch (Exception $e) {
            $contact->customSource = 'Api';
        }

        // If Test contact exists, remove it and create a new one
        if ($contact->id) {
            $contact->delete($contact->id);

            $contactObject = $api->getContactObject();
            $contact = $contactObject->createRow();
            $contact->email = $this->_sandboxEmail;
        }

        // Try to save with new info
        if (!$commonHelper->saveContact($contact)) {
            Mage::helper('bronto_roundtrip')->writeDebug('could not save contact');
            return false;
        }

        Mage::helper('bronto_roundtrip')->writeDebug('Added Contact');
        return $contact;
    }

    //  }}}
    //  {{{ _testAddContactToList()

    /**
     * Test adding contact to list
     *
     * @param Bronto_Common_Model_Api $api
     * @param Bronto_Api_Contact_Row  $contact
     *
     * @return boolean|Bronto_Api_Contact_Row
     * @access protected
     */
    protected function _testAddContactToList(
        Bronto_Common_Model_Api $api,
        Bronto_Api_Contact_Row $contact
    ) {
        // Set Status to Transactional
        $contact->status = Bronto_Api_Contact::STATUS_TRANSACTIONAL;

        if (!Mage::helper('bronto_common/contact')->saveContact($contact)) {
            Mage::helper('bronto_roundtrip')->writeDebug('could not save contact');
            return false;
        }

        if (!$this->_assertContactAttribute($api, $contact->email, 'status', Bronto_Api_Contact::STATUS_TRANSACTIONAL)) {
            return false;
        }

        // Get Lists
        $listObject = $api->getListObject();
        foreach ($listObject->readAll() as $list) {
            $contact->addToList($list['id']);
        }

        $contact->status = Bronto_Api_Contact::STATUS_ONBOARDING;

        if (!Mage::helper('bronto_common/contact')->saveContact($contact)) {
            Mage::helper('bronto_roundtrip')->writeDebug('could not save contact');
            return false;
        }

        if (!$this->_assertContactAttribute($api, $contact->email, 'status', Bronto_Api_Contact::STATUS_ONBOARDING)) {
            return false;
        }

        Mage::helper('bronto_roundtrip')->writeDebug('Updated Contact with transactional status');
        return $contact;
    }

    //  }}}
    //  {{{ _testProcessOrder()

    /**
     * Spoof processing an order
     *
     * @param Bronto_Common_Model_Api $api
     * @param Bronto_Api_Contact_Row  $contact
     *
     * @return boolean
     * @access protected
     */
    protected function _testProcessOrder(
        Bronto_Common_Model_Api $api,
        Bronto_Api_Contact_Row $contact
    ) {
        try {
            /* @var $orderObject Bronto_Api_Order */
            $orderObject = $api->getOrderObject();

            /* @var $brontoOrder Bronto_Api_Order_Row */
            $brontoOrder = $orderObject->createRow();
            $brontoOrder->id = substr(rand(), 0, 4);

            try {
                $brontoOrder->read();
            } catch (Exception $e) {
                //  do nothing
            }

            $brontoOrder->email     = $contact->email;
            $brontoOrder->orderDate = date('c', time());
            $brontoOrder->tid       = null;
            $brontoOrderItems = array(
                array(
                    'id'          => substr(rand(), 0, 4),
                    'sku'         => substr(rand(), 0, 10),
                    'name'        => 'sandbox sample product',
                    'description' => 'this is a fake product for testing',
                    'category'    => '1',
                    'image'       => null,
                    'url'         => 'http://www.atlanticbt.com',
                    'quantity'    => (int) '2',
                    'price'       => (float) '2.40',
                )
            );

            $brontoOrder->products = $brontoOrderItems;
            $brontoOrder->persist();

            $flushResult = $orderObject->flush();

            $flushResultErrors = array();
            foreach ($flushResult as $i => $flushResultRow) {
                if ($flushResultRow->hasError()) {
                    $flushResultErrors[] = array(
                        'errorCode'    => $flushResultRow->getErrorCode(),
                        'errorMessage' => $flushResultRow->getErrorMessage(),
                    );
                }
            }

            if (count($flushResultErrors)) {
                Mage::helper('bronto_roundtrip')->writeDebug(print_r($flushResultErrors, true));
                Mage::log(print_r($orderObject, true), null, 'roundtrip_error.log');
                return false;
            }


            // Cleanup
            $brontoOrder->delete();

            return true;
        } catch (Exception $e) {
            Mage::helper('bronto_roundtrip')->writeError($e->getMessage());
            //  what happens to the bronto order
            if ($brontoOrder->id) {
                $brontoOrder->delete();
            }
            return false;
        }
    }

    //  }}}
}
