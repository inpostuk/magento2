<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\AddressExtensionInterface;
use Magento\Quote\Api\Data\AddressExtensionInterfaceFactory;
use Inpost\Lockers\Model\Carrier\Inpost as Carrier;
use Inpost\Lockers\Api\Checkout\AddressRepositoryInterface;
use Inpost\Lockers\Api\Data\Checkout\AddressInterface;
use Inpost\Lockers\Api\Data\Checkout\AddressInterfaceFactory;

/**
 * Class LoadShippingAddressObserver
 * @package Inpost\Lockers\Observer
 */
class LoadShippingAddressObserver implements ObserverInterface
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AddressExtensionInterfaceFactory
     */
    private $addressExtensionFactory;

    /**
     * SaveCheckoutFieldsObserver constructor.
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressExtensionInterfaceFactory $addressExtensionFactory
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        AddressExtensionInterfaceFactory $addressExtensionFactory
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->addressExtensionFactory = $addressExtensionFactory;
    }

    /**
     * Load quote shipping address locker machine identifier
     * Triggered by:
     *      - sales_quote_address_load_after
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($observer->hasData('shipping_assignment')) {
            /** @var ShippingAssignmentInterface $shippingAssignment */
            $shippingAssignment = $observer->getData('shipping_assignment');
            $quoteAddress = $shippingAssignment->getShipping()->getAddress();
        } elseif ($observer->hasData('quote_address')) {
            $quoteAddress = $observer->getData('quote_address');
        }

        /** @var Address $quoteAddress */
        if (!$quoteAddress || $quoteAddress->getAddressType() !== Address::ADDRESS_TYPE_SHIPPING
            || $quoteAddress->getShippingMethod() !== Carrier::CODE . '_' . Carrier::METHOD
        ) {
            return;
        }

        try {
            /** @var AddressInterface $lockerAddress */
            $lockerAddress = $this->addressRepository->getByQuoteAddressId($quoteAddress->getId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        $extensionAttributes = $quoteAddress->getExtensionAttributes();
        if (!($extensionAttributes instanceof AddressExtensionInterface)) {
            $extensionAttributes = $this->addressExtensionFactory->create();
        }

        $extensionAttributes->setLockerMachine($lockerAddress->getLockerMachine());
        $quoteAddress->setExtensionAttributes($extensionAttributes);
    }
}
