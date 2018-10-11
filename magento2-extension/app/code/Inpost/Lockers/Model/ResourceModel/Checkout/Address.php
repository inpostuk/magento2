<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\ResourceModel\Checkout;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Inpost\Lockers\Api\Data\Checkout\AddressInterface;

/**
 * Class Address
 * @package MageToolkit\InPost\Model\ResourceModel\Checkout
 */
class Address extends AbstractDb
{
    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('post_locker_machine_checkout_address', AddressInterface::ENTITY_ID);
        $this->addUniqueField([
            'field' => [AddressInterface::SHIPPING_ADDRESS_ID],
            'title' => __('Locker Machine address with the same quote address')
        ]);
    }
}
