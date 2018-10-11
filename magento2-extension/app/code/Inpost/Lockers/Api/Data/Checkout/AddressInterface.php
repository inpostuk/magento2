<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Api\Data\Checkout;

interface AddressInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    const LOCKER_MACHINE = 'locker_machine';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getShippingAddressId();

    /**
     * @return string|null
     */
    public function getLockerMachine();

    /**
     * @param int $entityId
     * @return AddressInterface
     */
    public function setId($entityId);

    /**
     * @param int $addressId
     * @return AddressInterface
     */
    public function setShippingAddressId($addressId);

    /**
     * @param string $lockerMachine
     * @return AddressInterface
     */
    public function setLockerMachine($lockerMachine);
}
