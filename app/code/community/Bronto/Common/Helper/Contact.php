<?php

/**
 * @package   Bronto\Common
 * @copyright 2011-2012 Bronto Software, Inc.
 */
class Bronto_Common_Helper_Contact extends Bronto_Common_Helper_Data
{
    /**
     * @param string $email
     * @param string $customSource
     * @param int    $store
     *
     * @return Bronto_Api_Contact_Row
     */
    public function getContactByEmail($email, $customSource = null, $store = null)
    {
        if (empty($email)) {
            return false;
        }

        /* @var $contactObject Bronto_Api_Contact */
        $api           = $this->getApi(null, 'store', $store);
        $contactObject = $api->getContactObject();

        // Load Contact
        $contact        = $contactObject->createRow(array('email' => $email));
        $contact->email = $email;
        try {
            $contact = $contact->read();
        } catch (Exception $e) {
            // Contact doesn't exist
            $this->writeDebug('No Contact exists with email: ' . $email);
            // Set customSource if available
            if (!empty($customSource)) {
                $contact->customSource = $customSource;
            }
        }

        return $contact;
    }

    /**
     * @param Bronto_Api_Contact_Row $contact
     * @param bool                   $persistOnly
     *
     * @return Bronto_Api_Contact_Row
     */
    public function saveContact(Bronto_Api_Contact_Row $contact, $persistOnly = false)
    {

        if ($this->isTestModeEnabled()) {
            if (!$contact->id) {
                // Check for @bronto.com
                $parts = explode('@', $contact->email);
                if (isset($parts[1]) && $parts[1] == 'bronto.com') {
                    $this->writeInfo('TEST MODE: Contact is @bronto.com, allowing...');
                } else {
                    // User doesn't exist and isn't @bronto
                    $this->writeInfo('TEST MODE: Not updating Contact with email: ' . $contact->email);

                    return $contact;
                }
            }
        }

        if ($persistOnly) {
            $contact->persist();
        } else {
            try {
                if ($contact->id) {
                    $this->writeDebug("Updating existing Contact: ({$contact->email})...");
                } else {
                    $this->writeDebug("Saving new Contact: ({$contact->email})...");
                }
                $contact->save(false);
            } catch (Exception $e) {
                $this->writeError($e);
            }
        }

        $this->writeVerboseDebug('===== CONTACT SAVE =====', 'bronto_common_api.log');
        $this->writeVerboseDebug(var_export($contact->getApi()->getLastRequest(), true), 'bronto_common_api.log');
        $this->writeVerboseDebug(var_export($contact->getApi()->getLastResponse(), true), 'bronto_common_api.log');

        return $contact;
    }
}
