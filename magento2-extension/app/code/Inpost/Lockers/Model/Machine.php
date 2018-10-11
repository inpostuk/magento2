<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model;

class Machine extends \Magento\Framework\Model\AbstractModel implements
    MachineInterface,
    \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'inpost_lockers_machine';

    public function _construct()
    {
        $this->_init(\Inpost\Lockers\Model\ResourceModel\Machine::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function updateAttributes($attributes) {
        $this->setData($attributes);
        $this->save();
    }
}
