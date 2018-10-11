<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\ResourceModel\Machine;

class DataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init('Inpost\Lockers\Model\Machine', 'Inpost\Lockers\Model\ResourceModel\Machine');
    }
}
