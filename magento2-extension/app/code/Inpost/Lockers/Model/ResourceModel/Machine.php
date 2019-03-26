<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\ResourceModel;

class Machine extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('inpost_machine', 'machine_id');
    }

    public function removeMachineById($machine)
    {
        if ($machine->getId()) {
            $this->delete($machine);
        }
    }
}
