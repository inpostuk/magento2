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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Model\Quote\Address;
use Inpost\Lockers\Model\Carrier\Inpost as Carrier;
use Inpost\Lockers\Api\Checkout\AddressRepositoryInterface;
use Inpost\Lockers\Api\Data\Checkout\AddressInterface;
use Inpost\Lockers\Api\Data\Checkout\AddressInterfaceFactory;

/**
 * Class SaveShippingAddressObserver
 * @package Inpost\Lockers\Observer
 */
class SaveShippingAddressObserver implements ObserverInterface
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /** @var \Magento\Framework\App\RequestInterface  */
    private $request;

    /**
     * SaveShippingAddressObserver constructor
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
    
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->request = $request;
    }

    /**
     * Save quote shipping address locker machine identifier
     * Triggered by:
     *      - sales_quote_address_save_after
     *
     * @param Observer $observer
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Address $quoteAddress */
        $quoteAddress = $observer->getData('quote_address');

        if ($quoteAddress->getAddressType() !== Address::ADDRESS_TYPE_SHIPPING
            || $quoteAddress->getShippingMethod() !== Carrier::CODE . '_' . Carrier::METHOD
        ) {
            return;
        }

        if ($this->request->getParam('paypal_express')) {
            $lockerId = $this->request->getParam('locker_id');
            if (!$lockerId) {
                throw new LocalizedException(__('Please, select locker machine'));
            }
        } else {
            if (!$quoteAddress->getExtensionAttributes()
                || !$quoteAddress->getExtensionAttributes()->getLockerMachine()
            ) {
                if ($this->addressRepository->getByQuoteAddressId($quoteAddress->getId())->getId()) {
                    return true;
                }
                throw new LocalizedException(__('Please, select locker machine'));
            }
            $extensionAttributes = $quoteAddress->getExtensionAttributes();
            $lockerId = $extensionAttributes->getLockerMachine();
        }

        try {
            $lockerAddress = $this->addressRepository->getByQuoteAddressId($quoteAddress->getId());
        } catch (NoSuchEntityException $e) {
            $lockerAddress = $this->addressFactory->create();
            $lockerAddress->setShippingAddressId($quoteAddress->getId());
        }

        $lockerAddress->setLockerMachine($lockerId);
        $this->addressRepository->save($lockerAddress);
    }
}
