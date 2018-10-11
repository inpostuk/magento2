<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Block\Adminhtml\Shipping\View;

class Form extends \Magento\Shipping\Block\Adminhtml\View\Form
{
    public function getCreateLabelButton()
    {
        if ($this->getShipment()->getOrder()->getShippingMethod() !== 'inpost_inpost') {
            $data['shipment_id'] = $this->getShipment()->getId();
            $url = $this->getUrl('adminhtml/order_shipment/createLabel', $data);
            return $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Create Shipping Label...'),
                    'onclick' => 'packaging.showWindow();',
                    'class' => 'action-create-label'
                ]
            )->toHtml();
        }
    }
}
