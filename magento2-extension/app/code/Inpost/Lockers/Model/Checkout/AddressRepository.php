<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\Checkout;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Inpost\Lockers\Api\Data\Checkout\AddressInterface;
use Inpost\Lockers\Api\Checkout\AddressRepositoryInterface;
use Inpost\Lockers\Model\ResourceModel\Checkout\Address as ResourceAddress;

/**
 * Class AddressRepository
 * @package Inpost\Lockers\Model\Checkout
 */
class AddressRepository implements AddressRepositoryInterface
{
    /**
     * @var ResourceAddress
     */
    protected $resource;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * AddressRepository constructor
     *
     * @param ResourceAddress $resource
     * @param AddressFactory $addressFactory
     */
    public function __construct(ResourceAddress $resource, AddressFactory $addressFactory)
    {
        $this->resource = $resource;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Load address by entity id
     *
     * @param string|int $addressId
     * @return AddressInterface
     * @throws NoSuchEntityException
     */
    public function getById($addressId)
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $this->resource->load($address, $addressId);

        if (!$address->getId()) {
            throw new NoSuchEntityException(
                __('InPost checkout address with id "%1" does not exist.', $addressId)
            );
        }

        return $address;
    }

    /**
     * Load address by quote address id
     *
     * @param string|int $quoteAddressId
     * @return AddressInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteAddressId($quoteAddressId)
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $this->resource->load($address, $quoteAddressId, AddressInterface::SHIPPING_ADDRESS_ID);

        if (!$address->getId()) {
            throw new NoSuchEntityException(
                __('InPost checkout address for quote address id "%1" does not exist.', $quoteAddressId)
            );
        }

        return $address;
    }

    /**
     * Save address
     *
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws CouldNotSaveException
     */
    public function save(AddressInterface $address)
    {
        try {
            $this->resource->save($address);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $address;
    }
}
