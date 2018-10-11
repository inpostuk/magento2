<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Cron;

use Magento\Framework\Notification\NotifierInterface as NotifierPool;

class UpdateOrders
{
    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    private $orderCollection;
    /** @var \Inpost\Lockers\Adapter\Client */
    private $adapter;
    /** @var \Inpost\Lockers\Helper\Lockers */
    private $helper;
    /** @var \Magento\Sales\Model\Order\Status\History  */
    private $history;
    /** @var NotifierPool  */
    private $notifierPool;

    const MAPPING = [
        'delivered' => [
            'state' => 'complete',
            'status' => 'inpost_delivered'
        ],
        'delivered_to_agency' => [
            'state' => 'complete',
            'status' => 'inpost_deliveredtoagency'
        ],
        'cancelled' => [
            'state' => 'complete',
            'status' => 'inpost_cancelled'
        ],
        'claimed' => [
            'state' => 'complete',
            'status' => 'inpost_claimed'
        ],
        'created' => [
            'state' => 'complete',
            'status' => 'inpost_shipped'
        ],
        'prepared' => [
            'state' => 'complete',
            'status' => 'inpost_shipped'
        ],
        'sent' => [
            'state' => 'complete',
            'status' => 'inpost_shipped'
        ],
        'in_transit' => [
            'state' => 'complete',
            'status' => 'inpost_shipped'
        ],
        'stored' => [
            'state' => 'complete',
            'status' => 'inpost_shipped'
        ],
        'avizo' => [
            'state' => 'complete',
            'status' => 'inpost_stored'
        ],
        'expired' => [
            'state' => 'complete',
            'status' => 'inpost_expired'
        ],
        'retuned_to_agency' => [
            'state' => 'complete',
            'status' => 'inpost_returnedtoagency'
        ],
        'label_expired' => [
            'state' => 'complete',
            'status' => 'inpost_labelexpired'
        ],
        'not_delivered' => [
            'state' => 'complete',
            'status' => 'inpost_notdelivered'
        ],
        'missing' => [
            'state' => 'complete',
            'status' => 'inpost_missing'
        ]
    ];

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Inpost\Lockers\Adapter\Client $adapter,
        \Inpost\Lockers\Helper\Lockers $helper,
        \Magento\Sales\Model\Order\Status\History $history,
        NotifierPool $notifierPool
    ) {
    
        $this->orderCollection = $orderCollection;
        $this->adapter = $adapter;
        $this->helper = $helper;
        $this->history = $history;
        $this->notifierPool = $notifierPool;
    }

    public function execute()
    {
        $fromDate = date('Y-m-d' . ' 00:00:00', strtotime('-7 day'));
        $toDate = date('Y-m-d' . ' 23:59:59', time());
        $this->orderCollection
            ->addFieldToFilter('status', ['in' => ['in' => [
                'inpost_shipped',
                'inpost_delivered',
                'inpost_stored',
                'inpost_expired',
                'inpost_returnedtoagency',
                'inpost_labelexpired',
                'inpost_notdelivered',
                'inpost_missing'
            ]]])
            ->addFieldToFilter('created_at', [
                'from' => $fromDate,
                'to' => $toDate,
                'date' => true,
            ])
            ->addFieldToFilter('shipping_method', ['like' => '%inpost_inpost%']);
        if ($this->helper->isActive()) {
            $counter = [
                'avizo' => [
                    'counter' => 0,
                    'search' => 'parcels that haven’t been picked up by customers',
                    'message' => 'InPost: %s parcels that haven’t been picked
                     up by customers from lockers yet (InPost Stored24)'
                ],
                'expired' => [
                    'counter' => 0,
                    'search' => 'parcels that have expired',
                    'message' => 'InPost: %s parcels that have expired (InPost Expired)'
                ],
                'delivered_to_agency' => [
                    'counter' => 0,
                    'search' => 'parcels that have been returned',
                    'message' => 'InPost: %s parcels that have been returned (InPost Delivered2Agency)'
                ],
                'missing' => [
                    'counter' => 0,
                    'search' => 'missing parcels',
                    'message' => 'InPost: %s missing parcels (InPost Missing)'
                ],
                'label_expired' => [
                    'counter' => 0,
                    'search' => 'parcels with labels expired',
                    'message' => 'InPost: %s parcels with labels expired (InPost LabelExpired)'
                ],
            ];
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($this->orderCollection as $order) {
                /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                foreach ($order->getShipmentsCollection() as $shipment) {
                    /** @var \Magento\Sales\Model\Order\Shipment\Track $track * */
                    foreach ($shipment->getTracksCollection() as $track) {
                        if ($track->getCarrierCode() == 'inpost') {
                            $parcelData = $this->adapter->getParcelData($track->getTrackNumber());
                            if (array_key_exists($parcelData->status, self::MAPPING)) {
                                if (self::MAPPING[$parcelData->status]['status'] != $order->getStatus()) {
                                    $order->setState(self::MAPPING[$parcelData->status]['state']);
                                    $order->addStatusHistoryComment(
                                        '',
                                        self::MAPPING[$parcelData->status]['status']
                                    )
                                        ->setIsCustomerNotified(false)
                                        ->setEntityName('order');
                                    $order->save();
                                    if (array_key_exists($parcelData->status, $counter)) {
                                        $counter[$parcelData->status]['counter']++;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            foreach ($counter as $key => $value) {
                if ($value['counter'] > 0) {
                    $this->notifierPool->addNotice('InPost', sprintf($value['message'], $value['counter']));
                }
            }
        }
    }
}
