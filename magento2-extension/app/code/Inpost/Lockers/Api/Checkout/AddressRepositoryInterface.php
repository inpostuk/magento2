<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Api\Checkout;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Inpost\Lockers\Api\Data\Checkout\AddressInterface;

/**
 * Interface AddressRepositoryInterface
 * @package Inpost\Lockers\Api\Checkout
 */
interface AddressRepositoryInterface
{
    /**
     * Load address by entity id
     *
     * @param string|int $addressId
     * @return AddressInterface
     * @throws NoSuchEntityException
     */
    public function getById($addressId);

    /**
     * Load address by quote address id
     *
     * @param string|int $quoteAddressId
     * @return AddressInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteAddressId($quoteAddressId);

    /**
     * Save address
     *
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws CouldNotSaveException
     */
    public function save(AddressInterface $address);
}
