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
use Magento\Sales\Model\Order;

class SalesOrderShipmentSaveAfter implements ObserverInterface
{

    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    

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
        if ($parcelCreateWithMagento && $order->getShippingMethod() == 'inpost_inpost') {
            $orderState = Order::STATE_COMPLETE;
            $order->setState($orderState)->setStatus('inpost_shipped');
            $order->save();
        }
    }
}
