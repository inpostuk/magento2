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
use Magento\Framework\App\Filesystem\DirectoryList;

class SalesOrderShipmentSaveBefore implements ObserverInterface
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
    private $scopeConfig;

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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $shipment = $observer->getShipment();
        $order = $shipment->getOrder();
        $parcelCreateWithMagento = $this->scopeConfig->getValue(
            'carriers/inpost/parcel_create',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($parcelCreateWithMagento && $order->getShippingMethod() == 'inpost_inpost' && $this->helper->isActive()) {
            if ($order->getInvoiceCollection()->getSize() == 0) {
                if ($order->canInvoice()) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->register();
                    $invoice->save();
                    $transactionSave = $this->transaction->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();
                }
            }
            $params = $this->request->getParams();
            if (array_key_exists('parcel_weight', $params) && array_key_exists('size', $params)) {
                $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
                $quoteAddressId = $quote->getShippingAddress()->getId();
                if ($quoteAddressId) {
                    $shippingAddress = $order->getShippingAddress();
                    $machineId = $this->addressRepository->getByQuoteAddressId($quoteAddressId)->getLockerMachine();
                    $this->adapter->setToken($this->helper->getApiToken());
                    $weight = $this->calculateWeight($params['parcel_weight']);
                    $parcel = $this->adapter->createParcel(
                        $shippingAddress->getTelephone(),
                        $machineId,
                        $params['size'],
                        $weight,
                        $shippingAddress->getEmail(),
                        $order->getIncrementId(),
                        true
                    );
                    $order->setData('parcel_data', $parcel);
                    $order->save();
                    $parcel = json_decode($parcel);
                    $data = [
                        'carrier_code' => 'inpost',
                        'title' => 'InPost 24/7 Lockers',
                        'number' => $parcel->id,
                    ];
                    $track = $this->trackFactory->create()->addData($data);
                    $shipment->addTrack($track);
                    $this->adapter->pay($parcel->id);
                    $label = $this->adapter->getLabel($parcel->id, $this->helper->getLabelsFormat());
                    $labelPath = sprintf(
                        '%s%s_%s.%s',
                        $this->getLabelPath(),
                        $order->getIncrementId(),
                        $parcel->id,
                        $this->helper->getLabelsFormat()
                    );
                    file_put_contents($labelPath, $label);
                    $outputPdf = new \Zend_Pdf($labelPath, null, true);
                    $shipment->setShippingLabel($outputPdf->render());
                }
            }
        }
        return;
    }

    protected function calculateWeight($weight)
    {
        $metric = $this->helper->getMetric();
        switch ($metric) {
            case 'g':
                $finalWeight = $weight;
                break;
            case 'kg':
                $finalWeight = $weight * 1000;
                break;
            case 'lb':
                $finalWeight = $weight * 453.592;
                break;
            default:
                $finalWeight = $weight;
                break;
        }

        return $finalWeight;
    }

    protected function getLabelPath()
    {
        $ds = DIRECTORY_SEPARATOR;
        if (!file_exists(BP . $ds . 'pub')) {
            mkdir(BP . $ds . 'pub');
        }
        if (!file_exists(BP . $ds . 'pub' . $ds . 'media')) {
            mkdir(BP . $ds . 'pub' . $ds . 'media');
        }
        if (!file_exists(BP . $ds . 'pub' . $ds . 'media' . $ds . 'inpost')) {
            mkdir(BP . $ds . 'pub' . $ds . 'media' . $ds . 'inpost');
        }
        if (!file_exists(BP . $ds . 'pub' . $ds . 'media' . $ds . 'inpost' . $ds . 'shipping-labels')) {
            mkdir(BP . $ds . 'pub' . $ds . 'media' . $ds . 'inpost' . $ds . 'shipping-labels');
        }
        return BP . $ds . 'pub' . $ds . 'media' . $ds . 'inpost' . $ds . 'shipping-labels' . $ds;
    }
}
