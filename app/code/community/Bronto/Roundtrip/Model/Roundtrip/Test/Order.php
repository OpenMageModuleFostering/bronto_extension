<?php

/**
 * Test the order request
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
 * Test the order request
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
class Bronto_Roundtrip_Model_Roundtrip_Test_Order
{
    //  {{{ processOrder()

    /**
     * Process a stub order over to Bronto
     *
     * @param Bronto_Api_Order       $order
     * @param Bronto_Api_Contact_Row $contact contact to associate to order
     *
     * @return boolean
     * @access public
     */
    public function processOrder(
        Bronto_Api_Order $order,
        Bronto_Api_Contact_Row $contact
    ) {
        try {
            $helper = Mage::helper('bronto_roundtrip');
            /* @var $row Bronto_Api_Order_Row */
            $row = $order->createRow();
            //  Get new increment so we don't conflict w/ any existing
            //  increment id
            $incrementId = Mage::getSingleton('eav/config')
                ->getEntityType('order')
                ->fetchNewIncrementId(1);

            $row->email     = $contact->email;
            $row->contactId = $contact->id;
            $row->id        = $incrementId;
            $row->orderDate = date('c', time());

            $row->addProduct(
                array(
                    'id'          => substr(rand(), 0, 4),
                    'sku'         => substr(rand(), 0, 10),
                    'name'        => 'Sandbox Sample Product',
                    'description' => 'This is a fake product for testing',
                    'category'    => 1,
                    'image'       => NULL,
                    'url'         => 'http://www.atlanticbt.com/',
                    'quantity'    => 2,
                    'price'       => 2.40,
                )
            );

            $row->persist();
            $writeCache = $order->flush();

            //  flush out the write cache
            $errors = array();
            foreach ($writeCache as $row) {
                if ($row->hasError()) {
                    $errors[] = array(
                        'errorCode' => $row->getErrorCode(),
                        'errorMessage' => $row->getErrorMessage(),
                    );
                }
            }

            if (count($errors)) {
                $helper->writeDebug(print_r($errors, true));
                $helper->writeError(print_r($errors, true));
                return false;
            }

            $order->delete();
            return true;
        } catch (Exception $e) {
            $helper->writeError($e);
        }
    }

    //  }}}
}
