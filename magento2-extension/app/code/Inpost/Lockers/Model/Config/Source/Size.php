<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\Config\Source;

class Size implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'a', 'label' => __('A')],
            ['value' => 'b', 'label' => __('B')],
            ['value' => 'c', 'label' => __('C')]
        ];
    }

    public function toArray()
    {
        return ['a' => __('A'), 'b' => __('B'), 'c' => __('C')];
    }
}
