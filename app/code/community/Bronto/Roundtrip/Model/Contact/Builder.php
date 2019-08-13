<?php

/**
 * Roundtrip contact creator
 *
 * PHP version 5
 *
 * The license text...
 *
 * @category  Bronto
 * @package   Roundtrip
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   CVS: $Id:$
 * @link      <>
 * @see       References to other sections (if any)...
 */

/**
 * Roundtrip contact creator
 *
 * @category  Bronto
 * @package   Roundtrip
 * @author    Jamie Kahgee <jamie.kahgee@atlanticbt.com>
 * @copyright 2012 Atlantic BT
 * @license   http://www.atlanticbt.com/ Atlantic BT
 * @version   Release: @package_version@
 * @link      <>
 * @see       References to other sections (if any)...
 */
class Bronto_Roundtrip_Model_Contact_Builder
{
    //  {{{ properties

    const EMAIL = 'ps-eng@bronto.com';

    protected $_api;

    //  }}}
    //  {{{ __construct()

    public function __construct(Bronto_Common_Model_Api $api)
    {
        $this->_api = $api;
    }

    //  }}}
    //  {{{ _buildContact()

    protected function _buildContact()
    {
        $contactObject = $this->_api->getContactObject();
        $contact = $contactObject->createRow();
        $contact->email = self::EMAIL;

        // Get Contact Info
        try {
            $contact->read();
        } catch (Exception $e) {
            $contact->customSource = 'Api';
        }

        // If Test contact exists, remove it and create a new one
        if ($contact->id) {
            $contact->delete($contact->id);

            $contactObject = $this->_api->getContactObject();
            $contact = $contactObject->createRow();
            $contact->email = self::EMAIL;
        }

        return $contact;
    }

    //  }}}
    //  {{{ getContact()

    /**
     * Create a Contact in bronto
     *
     * @return boolean|Bronto_Api_Contact_Row
     * @access public
     */
    public function getContact()
    {
        // Load Contact
        $contact = $this->_buildContact();

        $helper = Mage::helper('bronto_roundtrip');

        // Try to save with new info
        if (!Mage::helper('bronto_common/contact')->saveContact($contact)) {
            $helper->writeDebug('could not save contact');
            return false;
        }

        $helper->writeDebug('Added Contact');
        return $contact;
    }

    //  }}}

    public function deleteContact()
    {
    }
}
