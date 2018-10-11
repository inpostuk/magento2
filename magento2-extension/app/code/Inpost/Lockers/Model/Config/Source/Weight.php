<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\Config\Source;

class Weight implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'kg', 'label' => __('kg')], ['value' => 'lb', 'label' => __('lb')]];
    }
    
    public function toArray()
    {
        return ['lb' => __('lb'), 'kg' => __('kg')];
    }
}
