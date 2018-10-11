<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Block\Adminhtml\Shipping;

class Parcel extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    public function isAvailable()
    {
        $shipment = $this->_coreRegistry->registry('current_shipment');
        $order = $shipment->getOrder();
        if ($order->getShippingMethod() == 'inpost_inpost') {
            return true;
        }
        return false;
    }

    public function getTotalWeight()
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->_coreRegistry->registry('current_shipment');
        $totalWeight = 0;
        foreach ($shipment->getAllItems() as $item) {
            $totalWeight += (float)$item->getWeight() * $item->getQty();
        }
        return $totalWeight;
    }

    public function getDefaultWeight()
    {
        return $this->_scopeConfig->getValue('carriers/inpost/weight');
    }

    public function getDefaultSize()
    {
        return $this->_scopeConfig->getValue('carriers/inpost/size_class');
    }

    public function getWeightConfig()
    {
        $shipment = $this->_coreRegistry->registry('current_shipment');
        $config = [];
        foreach ($shipment->getAllItems() as $item) {
            $config[$item->getOrderItemId()] = (float)$item->getWeight();
        }
        return json_encode($config);
    }
}
