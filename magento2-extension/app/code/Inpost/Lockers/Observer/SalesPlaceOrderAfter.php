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
use Inpost\Lockers\Api\Checkout\AddressRepositoryInterface;

class SalesPlaceOrderAfter implements ObserverInterface
{
    private $adapter;
    private $request;
    private $addressRepository;
    private $quoteFactory;
    private $machineResource;
    private $machine;
    private $helper;
    private $objectManager;
    private $trackFactory;
    private $filesystem;
    private $invoiceService;
    private $transaction;
    private $collection;

    public function __construct(
        \Inpost\Lockers\Adapter\Client $client,
        \Magento\Framework\App\RequestInterface $request,
        AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Inpost\Lockers\Model\ResourceModel\Machine $machineResource,
        \Inpost\Lockers\Model\Machine $machine,
        \Inpost\Lockers\Helper\Lockers $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Inpost\Lockers\Model\ResourceModel\Machine\DataCollection $collection
    ) {

        $this->adapter = $client;
        $this->helper = $helper;
        $this->filesystem = $filesystem;
        $this->objectManager = $objectManager;
        $this->invoiceService = $invoiceService;
        $this->request = $request;
        $this->addressRepository = $addressRepository;
        $this->quoteFactory = $quoteFactory;
        $this->machineResource = $machineResource;
        $this->machine = $machine;
        $this->trackFactory = $trackFactory;
        $this->transaction = $transaction;
        $this->collection = $collection;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order->getShippingMethod() == 'inpost_inpost' && $this->helper->isActive()) {
            $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
            $quoteAddressId = $quote->getShippingAddress()->getId();
            if ($quoteAddressId) {
                $shippingAddress = $order->getShippingAddress();
                $machineId = $this->addressRepository->getByQuoteAddressId($quoteAddressId)->getLockerMachine();
                $locker = $this->collection
                    ->addFieldToFilter('id', $machineId)
                    ->setPageSize(1, 1)
                    ->getLastItem();
                if ($locker->getId()) {
                    $shippingAddress->setPostcode($locker->getPostCode());
                    $shippingAddress->setStreet(
                        [
                            $locker->getStreet(),
                            "Locker ID ($machineId)"
                        ]
                    );
                    $shippingAddress->setCity($locker->getCity());
                    $shippingAddress->setCustomerAddressId(null);
                    $shippingAddress->setCompany($locker->getBuildingNo());
                    $shippingAddress->save();
                }
            }
        }
    }
}
