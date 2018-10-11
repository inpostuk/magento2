<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\Config\Source;

class Labels implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'pdf', 'label' => __('Pdf')], ['value' => 'zpl', 'label' => __('Zpl')]];
    }

    public function toArray()
    {
        return ['zpl' => __('Zpl'), 'pdf' => __('Pdf')];
    }
}
